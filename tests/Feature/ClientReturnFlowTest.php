<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ReturnType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A client asking to send delivered goods back. The request is only a request:
 * nothing moves until an admin approves it, so what matters here is that the
 * paperwork is honest — the order belongs to the asker, it has actually been
 * delivered, and nothing more than was bought can be sent back.
 */
class ClientReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Product $product;

    private ReturnType $returnType;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();
        $this->product = Product::factory()->create(['sale_price' => 100]);

        $this->returnType = ReturnType::create([
            'name' => 'Damaged on arrival',
            'disposition' => 'damage_section',
        ]);

        $this->order = $this->deliveredOrder($this->client);
    }

    public function test_a_client_can_ask_to_return_delivered_goods(): void
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.returns.store', $this->order), $this->payload())
            ->assertRedirect(route('client.returns.index'))
            ->assertSessionHasNoErrors();

        $return = OrderReturn::sole();

        $this->assertSame('pending', $return->status);
        $this->assertSame($this->client->id, $return->client_id);
        $this->assertSame(1, $return->items()->count());
        $this->assertSame('200.00', $return->items()->sole()->total_price);
    }

    public function test_an_order_that_has_not_been_delivered_cannot_be_returned(): void
    {
        $this->order->update(['status' => 'on_delivery']);

        $this->actingAs($this->client, 'client')
            ->post(route('client.returns.store', $this->order), $this->payload())
            ->assertForbidden();

        $this->assertSame(0, OrderReturn::count());
    }

    public function test_a_client_cannot_return_another_clients_order(): void
    {
        $stranger = Client::factory()->create();

        $this->actingAs($stranger, 'client')
            ->post(route('client.returns.store', $this->order), $this->payload())
            ->assertForbidden();

        $this->assertSame(0, OrderReturn::count());
    }

    public function test_more_than_was_ordered_cannot_be_returned(): void
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.returns.store', $this->order), $this->payload([
                'items' => [$this->item(['quantity' => 5])],
            ]))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, OrderReturn::count());
    }

    public function test_quantities_accumulate_across_separate_requests(): void
    {
        $this->actingAs($this->client, 'client')->post(route('client.returns.store', $this->order), $this->payload([
            'items' => [$this->item(['quantity' => 3])],
        ]));

        // 3 of 3 already asked for; nothing is left.
        $this->actingAs($this->client, 'client')
            ->post(route('client.returns.store', $this->order), $this->payload([
                'items' => [$this->item(['quantity' => 1])],
            ]))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(1, OrderReturn::count());
    }

    /**
     * Regression: the header row was written before the item loop and the
     * method ran outside a transaction, so a payload naming a line from
     * someone else's order left a committed, empty return sitting in the
     * admin queue.
     */
    public function test_a_line_from_another_order_leaves_nothing_behind(): void
    {
        $foreignOrder = $this->deliveredOrder(Client::factory()->create());
        $foreignItem = $foreignOrder->items()->sole();

        $this->actingAs($this->client, 'client')
            ->post(route('client.returns.store', $this->order), $this->payload([
                'items' => [['order_item_id' => $foreignItem->id, 'quantity' => 1]],
            ]))
            ->assertNotFound();

        $this->assertSame(0, OrderReturn::count());
    }

    private function deliveredOrder(Client $client): Order
    {
        $order = Order::create([
            'client_id' => $client->id,
            'status' => 'delivered',
            'payment_method' => 'cash_on_delivery',
            'subtotal' => 300,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 360,
            'delivery_charge' => 60,
            'created_by' => $client->id,
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 3,
            'unit_price' => 100,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_price' => 300,
        ]);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'return_type_id' => $this->returnType->id,
            'reason' => 'Two arrived broken',
            'items' => [$this->item()],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function item(array $overrides = []): array
    {
        return array_merge([
            'order_item_id' => $this->order->items()->sole()->id,
            'quantity' => 2,
        ], $overrides);
    }
}
