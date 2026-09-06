<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\OrderSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Client-placed orders, from submission through the admin status ladder.
 *
 * Pricing here is not taken from the request: the client posts products and
 * quantities only, and the server decides the price, the discount and the VAT.
 * The status ladder is one-step-at-a-time, so skipping a rung must be refused.
 */
class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private const DELIVERY_CHARGE = 60.0;

    private Client $client;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->client = Client::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->product = Product::factory()->create(['sale_price' => 100]);
    }

    public function test_a_client_can_place_an_order(): void
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.orders.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $order = Order::sole();

        $this->assertSame($this->client->id, $order->client_id);
        $this->assertSame('pending', $order->status);
        $this->assertSame(1, $order->items()->count());

        // 2 x 100 net, plus the flat delivery charge.
        $this->assertSame('200.00', $order->subtotal);
        $this->assertSame('60.00', $order->delivery_charge);
        $this->assertSame('260.00', $order->total_amount);
    }

    public function test_the_price_comes_from_the_catalogue_not_the_request(): void
    {
        // A tampered payload naming its own price must not be honoured.
        $this->actingAs($this->client, 'client')->post(route('client.orders.store'), $this->payload([
            'items' => [['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 1]],
        ]));

        $this->assertSame('100.00', Order::sole()->items()->sole()->unit_price);
    }

    public function test_an_active_percentage_discount_is_applied(): void
    {
        ProductDiscount::create([
            'product_id' => $this->product->id,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'applicable_to' => 'all',
            'status' => true,
        ]);

        $this->actingAs($this->client, 'client')->post(route('client.orders.store'), $this->payload());

        $order = Order::sole();

        // 200 net − 10% = 20 off, then delivery.
        $this->assertSame('20.00', $order->discount_amount);
        $this->assertSame('240.00', $order->total_amount);
    }

    public function test_an_expired_discount_is_ignored(): void
    {
        ProductDiscount::create([
            'product_id' => $this->product->id,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'applicable_to' => 'all',
            'status' => true,
        ]);

        $this->actingAs($this->client, 'client')->post(route('client.orders.store'), $this->payload());

        $this->assertSame('0.00', Order::sole()->discount_amount);
    }

    public function test_vat_is_charged_at_the_products_own_rate(): void
    {
        $vatProduct = Product::factory()->withVat(15)->create(['sale_price' => 100]);

        $this->actingAs($this->client, 'client')->post(route('client.orders.store'), $this->payload([
            'items' => [['product_id' => $vatProduct->id, 'quantity' => 2]],
        ]));

        $order = Order::sole();

        $this->assertSame('30.00', $order->vat_amount);
        $this->assertSame('290.00', $order->total_amount);
    }

    public function test_a_bank_order_must_carry_a_receipt(): void
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.orders.store'), $this->payload(['payment_method' => 'bank']))
            ->assertSessionHasErrors('payment_receipt');

        $this->assertSame(0, Order::count());
    }

    public function test_a_mobile_banking_order_must_carry_a_transaction_reference(): void
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.orders.store'), $this->payload(['payment_method' => 'mobile_banking']))
            ->assertSessionHasErrors('transaction_reference');
    }

    public function test_placing_an_order_notifies_the_admins(): void
    {
        $this->actingAs($this->client, 'client')->post(route('client.orders.store'), $this->payload());

        Notification::assertSentTo($this->admin, OrderSubmittedNotification::class);
    }

    public function test_a_client_cannot_open_another_clients_order(): void
    {
        $order = $this->placeOrder();
        $stranger = Client::factory()->create();

        $this->actingAs($stranger, 'client')
            ->get(route('client.orders.show', $order))
            ->assertForbidden();
    }

    public function test_an_admin_accepts_a_pending_order_and_the_client_is_told(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.accept', $order))
            ->assertSessionHas('success');

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(2, $order->statusLogs()->count());

        Notification::assertSentTo($this->client, OrderStatusChangedNotification::class);
    }

    public function test_an_order_that_is_already_moving_cannot_be_accepted_again(): void
    {
        $order = $this->placeOrder();
        $order->update(['status' => 'processing']);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.accept', $order))
            ->assertSessionHas('error');

        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_rejecting_a_pending_order_records_the_reason(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.reject', $order), ['reason' => 'Out of coverage area'])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('rejected', $order->status);
        $this->assertSame('Out of coverage area', $order->admin_note);
    }

    public function test_the_status_ladder_cannot_be_skipped(): void
    {
        $order = $this->placeOrder();

        // pending → confirmed skips the processing rung.
        $this->actingAs($this->admin)
            ->post(route('admin.orders.update-status', $order), ['status' => 'confirmed'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_confirming_an_order_opens_a_packaging_record(): void
    {
        $order = $this->placeOrder();
        $order->update(['status' => 'processing']);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.update-status', $order), ['status' => 'confirmed'])
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame($order->id, Packaging::sole()->order_id);
    }

    public function test_a_client_can_cancel_while_the_order_is_still_early(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->client, 'client')
            ->post(route('client.orders.cancel', $order), ['cancel_reason' => 'Ordered by mistake'])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('Ordered by mistake', $order->cancel_reason);
    }

    public function test_a_client_cannot_cancel_once_the_order_is_confirmed(): void
    {
        $order = $this->placeOrder();
        $order->update(['status' => 'confirmed']);

        $this->actingAs($this->client, 'client')
            ->post(route('client.orders.cancel', $order), ['cancel_reason' => 'Changed my mind'])
            ->assertSessionHas('error');

        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_guests_cannot_place_orders(): void
    {
        $this->post(route('client.orders.store'), $this->payload())
            ->assertRedirect(route('client.login'));

        $this->assertSame(0, Order::count());
    }

    private function placeOrder(): Order
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.orders.store'), $this->payload());

        return Order::sole();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'payment_method' => 'cash_on_delivery',
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
        ], $overrides);
    }
}
