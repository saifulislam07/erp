<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\DamageLog;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ReturnType;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use App\Services\CashBankService;
use App\Services\StockService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Approving a return has to decide where the goods go — back on the shelf or
 * into the damage log — and refund the customer either way. The disposition
 * comes from the return type, and the destination store from whichever store
 * originally shipped the order.
 */
class OrderReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $client;

    private Store $store;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['is_admin' => true, 'status' => true]);
        $this->admin->assignRole('Admin');

        $this->store = Store::create(['name' => 'Main warehouse', 'location' => 'Dhaka', 'status' => true]);
        $this->product = $this->product();
        $this->client = $this->client();
    }

    /**
     * The listing is only exercised against an empty table elsewhere, which
     * hides any wrong table name in the query — a return has to actually exist
     * before the DataTables feed runs its select.
     */
    public function test_the_returns_listing_renders_a_real_row(): void
    {
        $return = $this->pendingReturn();

        $response = $this->actingAs($this->admin)
            ->getJson(
                route('admin.returns.index', ['draw' => 1, 'start' => 0, 'length' => 10]),
                ['X-Requested-With' => 'XMLHttpRequest'],
            );

        $response->assertOk()->assertJsonPath('recordsTotal', 1);

        $row = $response->json('data.0');

        $this->assertSame($return->order->order_id, $row['order_label']);
        $this->assertSame('Rahim Traders', $row['client_name']);
        $this->assertSame('Damaged on arrival', $row['type_name']);
    }

    public function test_the_returns_listing_can_be_searched_with_a_real_row_present(): void
    {
        $return = $this->pendingReturn();

        $this->actingAs($this->admin)
            ->getJson(
                route('admin.returns.index', [
                    'draw' => 1,
                    'start' => 0,
                    'length' => 10,
                    'search' => ['value' => $return->return_id, 'regex' => 'false'],
                ]),
                ['X-Requested-With' => 'XMLHttpRequest'],
            )
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1);
    }

    public function test_approving_a_restockable_return_puts_the_goods_back_in_the_shipping_store(): void
    {
        $return = $this->pendingReturn(disposition: 'restock');

        $this->actingAs($this->admin)
            ->post(route('admin.returns.approve', $return), [
                'refund_amount' => 120,
                'refund_method' => 'cash',
            ])
            ->assertRedirect(route('admin.returns.show', $return));

        $this->assertSame(2.0, $this->availableStock(), 'the returned units should be back on the shelf');
        $this->assertSame(0, DamageLog::count());

        $return->refresh();
        $this->assertSame('approved', $return->status);
        $this->assertSame(120.0, (float) $return->refund_amount);
        $this->assertSame($this->admin->id, $return->approved_by);
        $this->assertNotNull($return->approved_at);
    }

    public function test_approving_a_damaged_return_logs_the_damage_instead_of_restocking(): void
    {
        $return = $this->pendingReturn(disposition: 'damage_section');

        $this->actingAs($this->admin)->post(route('admin.returns.approve', $return), [
            'refund_amount' => 120,
            'refund_method' => 'cash',
        ]);

        $this->assertSame(0.0, $this->availableStock(), 'damaged goods must not go back on the shelf');

        $damage = DamageLog::sole();
        $this->assertSame($this->product->id, $damage->product_id);
        $this->assertSame(2.0, (float) $damage->quantity);
        $this->assertSame($return->reason, $damage->reason);
    }

    public function test_approving_a_return_refunds_the_customer_from_the_cash_ledger(): void
    {
        $return = $this->pendingReturn();

        $this->actingAs($this->admin)->post(route('admin.returns.approve', $return), [
            'refund_amount' => 120,
            'refund_method' => 'cash',
        ]);

        $this->assertSame(-120.0, app(CashBankService::class)->getCashBalance());
    }

    public function test_a_return_cannot_be_approved_twice(): void
    {
        $return = $this->pendingReturn(disposition: 'restock');

        $payload = ['refund_amount' => 120, 'refund_method' => 'cash'];

        $this->actingAs($this->admin)->post(route('admin.returns.approve', $return), $payload);
        $this->actingAs($this->admin)
            ->post(route('admin.returns.approve', $return), $payload)
            ->assertSessionHas('error');

        $this->assertSame(2.0, $this->availableStock(), 'the goods must not be restocked twice');
        $this->assertSame(-120.0, app(CashBankService::class)->getCashBalance(), 'the refund must not be paid twice');
    }

    public function test_approving_requires_a_refund_amount_and_method(): void
    {
        $return = $this->pendingReturn();

        $this->actingAs($this->admin)
            ->post(route('admin.returns.approve', $return), [])
            ->assertSessionHasErrors(['refund_amount', 'refund_method']);

        $this->assertSame('pending', $return->fresh()->status);
    }

    public function test_rejecting_a_return_records_the_note_and_moves_no_stock_or_money(): void
    {
        $return = $this->pendingReturn(disposition: 'restock');

        $this->actingAs($this->admin)
            ->post(route('admin.returns.reject', $return), ['note' => 'Outside the return window'])
            ->assertRedirect(route('admin.returns.show', $return));

        $return->refresh();

        $this->assertSame('rejected', $return->status);
        $this->assertSame('Outside the return window', $return->note);
        $this->assertSame(0.0, $this->availableStock());
        $this->assertSame(0.0, app(CashBankService::class)->getCashBalance());
    }

    public function test_a_rejected_return_cannot_then_be_approved(): void
    {
        $return = $this->pendingReturn();

        $this->actingAs($this->admin)->post(route('admin.returns.reject', $return), ['note' => 'Too late']);

        $this->actingAs($this->admin)
            ->post(route('admin.returns.approve', $return), ['refund_amount' => 120, 'refund_method' => 'cash'])
            ->assertSessionHas('error');

        $this->assertSame('rejected', $return->fresh()->status);
    }

    /**
     * An order that was never dispatched has no store on record, so the restock
     * falls back to the first active store rather than losing the goods.
     */
    public function test_a_return_with_no_dispatch_record_restocks_to_the_first_active_store(): void
    {
        $return = $this->pendingReturn(disposition: 'restock', dispatched: false);

        $this->actingAs($this->admin)->post(route('admin.returns.approve', $return), [
            'refund_amount' => 120,
            'refund_method' => 'cash',
        ]);

        $this->assertSame(2.0, $this->availableStock());
    }

    private function availableStock(): float
    {
        return app(StockService::class)->getAvailableStock($this->product->id, $this->store->id);
    }

    private function pendingReturn(string $disposition = 'damage_section', bool $dispatched = true): OrderReturn
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'status' => 'delivered',
            'payment_method' => 'cash_on_delivery',
            'subtotal' => 600,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 600,
            'delivery_charge' => 0,
            'created_by' => $this->client->id,
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 60,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_price' => 600,
        ]);

        if ($dispatched) {
            $order->dispatchLog()->create([
                'store_id' => $this->store->id,
                'dispatched_by' => $this->admin->id,
                'dispatched_at' => now(),
            ]);
        }

        $returnType = ReturnType::create([
            'name' => 'Damaged on arrival',
            'disposition' => $disposition,
        ]);

        $return = OrderReturn::create([
            'order_id' => $order->id,
            'client_id' => $this->client->id,
            'return_type_id' => $returnType->id,
            'reason' => 'Two bags split in transit',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $return->items()->create([
            'order_item_id' => $orderItem->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 60,
            'total_price' => 120,
        ]);

        return $return;
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Grocery', 'status' => true]);
        $unit = Unit::create(['name' => 'Kg']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Basmati Rice',
            'unit_id' => $unit->id,
            'mrp_price' => 90,
            'purchase_price' => 40,
            'sale_price' => 60,
            'min_stock_threshold' => 0,
            'status' => true,
        ]);
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Rahim Traders',
            'email' => 'rahim@example.com',
            'phone' => '01800000000',
            'address' => '5 Market Street',
            'business_name' => 'Rahim Traders',
            'type' => 'client',
            'password' => 'password',
            'status' => true,
        ]);
    }
}
