<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreDispatchLog;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * An order walks pending → processing → confirmed → on_delivery → delivered,
 * but the steps are spread across three controllers: OrderController accepts
 * and confirms, StoreDispatchController hands it to a store, DeliveryController
 * closes it out. Each one re-checks the status it expects, and every hop is
 * meant to leave a status log and tell the client.
 */
class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $client;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['is_admin' => true, 'status' => true]);
        $this->admin->assignRole('Admin');

        $this->client = $this->client();
        $this->store = Store::create(['name' => 'Main warehouse', 'location' => 'Dhaka', 'status' => true]);
    }

    public function test_accepting_a_pending_order_moves_it_to_processing_and_logs_the_change(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.accept', $order))
            ->assertSessionHas('success');

        $this->assertSame('processing', $order->fresh()->status);

        $log = $order->statusLogs()->sole();
        $this->assertSame('pending', $log->from_status);
        $this->assertSame('processing', $log->to_status);
        $this->assertSame('admin', $log->changed_by_type);
        $this->assertSame($this->admin->id, $log->changed_by_id);

        Notification::assertSentTo($this->client, OrderStatusChangedNotification::class);
    }

    public function test_only_a_pending_order_can_be_accepted(): void
    {
        $order = $this->order(status: 'processing');

        $this->actingAs($this->admin)
            ->post(route('admin.orders.accept', $order))
            ->assertSessionHas('error');

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(0, $order->statusLogs()->count());
    }

    public function test_rejecting_an_order_records_the_reason(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.reject', $order), ['reason' => 'Out of coverage area'])
            ->assertSessionHas('success');

        $order->refresh();

        $this->assertSame('rejected', $order->status);
        $this->assertSame('Out of coverage area', $order->admin_note);
        $this->assertSame('Out of coverage area', $order->statusLogs()->sole()->note);
    }

    public function test_a_rejection_needs_a_reason(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.reject', $order), [])
            ->assertSessionHasErrors('reason');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_confirming_a_processing_order_creates_its_packaging_record(): void
    {
        $order = $this->order(status: 'processing');

        $this->actingAs($this->admin)
            ->post(route('admin.orders.update-status', $order), ['status' => 'confirmed'])
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->fresh()->status);

        $packaging = Packaging::sole();
        $this->assertSame($order->id, $packaging->order_id);
        $this->assertSame($this->admin->id, $packaging->packed_by);
    }

    /**
     * Only one hop is driven from the order screen — everything past `confirmed`
     * belongs to the dispatch and delivery workflows, so the status box must not
     * be usable to skip them.
     */
    public function test_the_order_screen_cannot_skip_ahead_of_the_dispatch_workflow(): void
    {
        $order = $this->order(status: 'processing');

        $this->actingAs($this->admin)
            ->post(route('admin.orders.update-status', $order), ['status' => 'delivered'])
            ->assertSessionHas('error');

        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_a_confirmed_order_cannot_be_advanced_from_the_order_screen(): void
    {
        $order = $this->order(status: 'confirmed');

        $this->actingAs($this->admin)
            ->post(route('admin.orders.update-status', $order), ['status' => 'on_delivery'])
            ->assertSessionHas('error');

        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_an_order_must_be_confirmed_before_it_can_be_packed(): void
    {
        $order = $this->order(status: 'processing');

        $this->actingAs($this->admin)
            ->post(route('admin.orders.pack', $order), ['notes' => 'Two cartons'])
            ->assertSessionHas('error');

        $this->assertSame(0, Packaging::count());
    }

    public function test_dispatching_a_confirmed_order_opens_a_delivery(): void
    {
        $order = $this->order(status: 'confirmed');

        $this->actingAs($this->admin)
            ->post(route('admin.store.dispatch', $order), ['store_id' => $this->store->id])
            ->assertRedirect(route('admin.store.dispatch-queue'));

        $this->assertSame('on_delivery', $order->fresh()->status);

        $dispatchLog = StoreDispatchLog::sole();
        $this->assertSame($this->store->id, $dispatchLog->store_id);

        $delivery = Delivery::sole();
        $this->assertSame('pending', $delivery->status);
        $this->assertSame($dispatchLog->id, $delivery->store_dispatch_log_id);
    }

    public function test_an_order_cannot_be_dispatched_twice(): void
    {
        $order = $this->order(status: 'confirmed');

        $this->actingAs($this->admin)
            ->post(route('admin.store.dispatch', $order), ['store_id' => $this->store->id]);

        // The order is now `on_delivery` and already has a dispatch log.
        $this->actingAs($this->admin)
            ->post(route('admin.store.dispatch', $order), ['store_id' => $this->store->id])
            ->assertSessionHas('error');

        $this->assertSame(1, StoreDispatchLog::count());
        $this->assertSame(1, Delivery::count());
    }

    public function test_an_unconfirmed_order_is_not_in_the_dispatch_queue(): void
    {
        $this->order(status: 'processing');
        $confirmed = $this->order(status: 'confirmed');

        $response = $this->actingAs($this->admin)->get(route('admin.store.dispatch-queue'));

        $response->assertOk();
        $response->assertViewHas('orders', fn ($orders) => $orders->pluck('id')->all() === [$confirmed->id]);
    }

    public function test_a_delivery_runs_from_pending_to_delivered(): void
    {
        $delivery = $this->dispatchedDelivery();

        $this->actingAs($this->admin)
            ->post(route('admin.deliveries.out', $delivery), ['delivery_person_name' => 'Karim'])
            ->assertSessionHas('success');

        $delivery->refresh();
        $this->assertSame('out_for_delivery', $delivery->status);
        $this->assertSame('Karim', $delivery->delivery_person_name);

        $this->actingAs($this->admin)
            ->post(route('admin.deliveries.delivered', $delivery))
            ->assertSessionHas('success');

        $delivery->refresh();
        $this->assertSame('delivered', $delivery->status);
        $this->assertNotNull($delivery->delivered_at);
        $this->assertSame('delivered', $delivery->order->fresh()->status);
    }

    public function test_a_delivery_cannot_be_completed_before_it_goes_out(): void
    {
        $delivery = $this->dispatchedDelivery();

        $this->actingAs($this->admin)
            ->post(route('admin.deliveries.delivered', $delivery))
            ->assertSessionHas('error');

        $this->assertSame('pending', $delivery->fresh()->status);
        $this->assertSame('on_delivery', $delivery->order->fresh()->status);
    }

    /**
     * A failed attempt sends the order back to `processing` so it can be
     * re-confirmed and dispatched again.
     */
    public function test_a_failed_delivery_sends_the_order_back_to_processing(): void
    {
        $delivery = $this->dispatchedDelivery();

        $this->actingAs($this->admin)
            ->post(route('admin.deliveries.out', $delivery), ['delivery_person_name' => 'Karim']);

        $this->actingAs($this->admin)
            ->post(route('admin.deliveries.failed', $delivery), ['delivery_note' => 'Nobody at the address'])
            ->assertSessionHas('success');

        $delivery->refresh();

        $this->assertSame('failed', $delivery->status);
        $this->assertSame('processing', $delivery->order->fresh()->status);

        $lastLog = $delivery->order->statusLogs()->get()->last();
        $this->assertSame('processing', $lastLog->to_status);
        $this->assertStringContainsString('Nobody at the address', $lastLog->note);
    }

    public function test_a_failed_delivery_needs_a_note(): void
    {
        $delivery = $this->dispatchedDelivery();

        $this->actingAs($this->admin)
            ->post(route('admin.deliveries.failed', $delivery), [])
            ->assertSessionHasErrors('delivery_note');

        $this->assertSame('pending', $delivery->fresh()->status);
    }

    /**
     * A Store Manager runs the warehouse floor end to end. The seeder grants
     * them `order.edit`, which satisfies both OrderPolicy::update and
     * ::dispatch — so they can accept an order as well as ship it.
     */
    public function test_a_store_manager_can_run_the_order_through_dispatch(): void
    {
        $storeManager = User::factory()->create(['status' => true]);
        $storeManager->assignRole('Store Manager');

        $order = $this->order();

        $this->actingAs($storeManager)
            ->post(route('admin.orders.accept', $order))
            ->assertSessionHas('success');

        $this->actingAs($storeManager)
            ->post(route('admin.orders.update-status', $order), ['status' => 'confirmed']);

        $this->actingAs($storeManager)
            ->post(route('admin.store.dispatch', $order), ['store_id' => $this->store->id])
            ->assertRedirect(route('admin.store.dispatch-queue'));

        $this->assertSame('on_delivery', $order->fresh()->status);
    }

    /**
     * An Employee holds only the sale permissions, so the order screens are
     * closed to them in both directions.
     */
    public function test_a_user_without_order_permissions_can_neither_accept_nor_dispatch(): void
    {
        $employee = User::factory()->create(['status' => true]);
        $employee->assignRole('Employee');

        $pending = $this->order();

        $this->actingAs($employee)
            ->post(route('admin.orders.accept', $pending))
            ->assertForbidden();

        $confirmed = $this->order(status: 'confirmed');

        $this->actingAs($employee)
            ->post(route('admin.store.dispatch', $confirmed), ['store_id' => $this->store->id])
            ->assertForbidden();

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame('confirmed', $confirmed->fresh()->status);
    }

    /**
     * The whole walk in one go, to prove the handoffs between the three
     * controllers line up.
     */
    public function test_the_full_lifecycle_leaves_a_complete_status_trail(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin)->post(route('admin.orders.accept', $order));
        $this->actingAs($this->admin)->post(route('admin.orders.update-status', $order), ['status' => 'confirmed']);
        $this->actingAs($this->admin)->post(route('admin.store.dispatch', $order), ['store_id' => $this->store->id]);

        $delivery = Delivery::sole();
        $this->actingAs($this->admin)->post(route('admin.deliveries.out', $delivery), ['delivery_person_name' => 'Karim']);
        $this->actingAs($this->admin)->post(route('admin.deliveries.delivered', $delivery));

        $this->assertSame('delivered', $order->fresh()->status);

        $this->assertSame(
            ['processing', 'confirmed', 'on_delivery', 'delivered'],
            $order->statusLogs()->pluck('to_status')->all(),
        );
    }

    private function dispatchedDelivery(): Delivery
    {
        $order = $this->order(status: 'confirmed');

        $this->actingAs($this->admin)
            ->post(route('admin.store.dispatch', $order), ['store_id' => $this->store->id]);

        return Delivery::where('order_id', $order->id)->sole();
    }

    private function order(string $status = 'pending'): Order
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'status' => $status,
            'payment_method' => 'cash_on_delivery',
            'subtotal' => 600,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 600,
            'delivery_charge' => 0,
            // Orders originate in the client portal, so `created_by` holds the
            // client's id rather than a user's.
            'created_by' => $this->client->id,
        ]);

        $order->items()->create([
            'product_id' => $this->product()->id,
            'quantity' => 10,
            'unit_price' => 60,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_price' => 600,
        ]);

        return $order;
    }

    private function product(): Product
    {
        $category = Category::firstOrCreate(['name' => 'Grocery'], ['status' => true]);
        $unit = Unit::firstOrCreate(['name' => 'Kg']);

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
