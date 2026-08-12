<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the batch maths behind every stock movement: which batch a sale eats
 * into first, what happens when there is not enough to go round, and that the
 * movement log tells the truth about the quantity before and after.
 *
 * An error here corrupts inventory silently — nothing else in the app
 * recalculates stock from scratch, so these numbers are the only record.
 */
class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stock;

    private User $user;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->stock = app(StockService::class);
        $this->user = User::factory()->create();
        $this->store = $this->store();
    }

    public function test_adding_stock_creates_a_batch_and_logs_the_movement(): void
    {
        $product = $this->product();

        $batch = $this->stock->addStock(
            productId: $product->id,
            storeId: $this->store->id,
            quantity: 25,
            purchasePrice: 40,
            expiryDate: null,
            referenceType: null,
            referenceId: null,
            createdBy: $this->user->id,
        );

        $this->assertSame(25.0, (float) $batch->quantity);
        $this->assertSame(25.0, $this->stock->getAvailableStock($product->id));

        $movement = StockMovement::sole();
        $this->assertSame('adjustment', $movement->movement_type);
        $this->assertSame(0.0, (float) $movement->before_quantity);
        $this->assertSame(25.0, (float) $movement->after_quantity);
    }

    /**
     * The whole point of the batch table: the oldest goods leave first.
     */
    public function test_deduction_consumes_the_oldest_batch_first(): void
    {
        $product = $this->product();

        $first = $this->batch($product, quantity: 10, createdAt: now()->subDays(3));
        $second = $this->batch($product, quantity: 10, createdAt: now()->subDays(2));
        $third = $this->batch($product, quantity: 10, createdAt: now()->subDay());

        $this->deduct($product, 15);

        $this->assertSame(0.0, (float) $first->fresh()->quantity, 'the oldest batch should be emptied first');
        $this->assertSame(5.0, (float) $second->fresh()->quantity, 'the remainder comes off the next batch');
        $this->assertSame(10.0, (float) $third->fresh()->quantity, 'the newest batch is untouched');
        $this->assertSame(15.0, $this->stock->getAvailableStock($product->id));
    }

    /**
     * Taking exactly one batch's worth must not spill into the next one.
     */
    public function test_deducting_an_exact_batch_quantity_leaves_the_next_batch_alone(): void
    {
        $product = $this->product();

        $first = $this->batch($product, quantity: 10, createdAt: now()->subDays(2));
        $second = $this->batch($product, quantity: 10, createdAt: now()->subDay());

        $this->deduct($product, 10);

        $this->assertSame(0.0, (float) $first->fresh()->quantity);
        $this->assertSame(10.0, (float) $second->fresh()->quantity);
    }

    /**
     * Batches created within the same second fall back to insertion order.
     */
    public function test_batches_created_at_the_same_moment_are_consumed_in_insertion_order(): void
    {
        $product = $this->product();

        $sameMoment = now();
        $first = $this->batch($product, quantity: 4, createdAt: $sameMoment);
        $second = $this->batch($product, quantity: 4, createdAt: $sameMoment);

        $this->deduct($product, 4);

        $this->assertSame(0.0, (float) $first->fresh()->quantity);
        $this->assertSame(4.0, (float) $second->fresh()->quantity);
    }

    public function test_deducting_more_than_is_available_throws_and_changes_nothing(): void
    {
        $product = $this->product();

        $batch = $this->batch($product, quantity: 5);

        try {
            $this->deduct($product, 6);
            $this->fail('Expected a RuntimeException for insufficient stock.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Insufficient stock', $e->getMessage());
        }

        $this->assertSame(5.0, (float) $batch->fresh()->quantity, 'a rejected deduction must not touch the batch');
        $this->assertSame(0, StockMovement::count(), 'a rejected deduction must not be logged');
    }

    /**
     * Availability is per-store on the way out, so a full warehouse elsewhere
     * cannot be used to satisfy a sale from an empty one.
     */
    public function test_stock_in_another_store_cannot_satisfy_a_deduction(): void
    {
        $product = $this->product();
        $otherStore = $this->store('Second warehouse');

        $elsewhere = Stock::create([
            'product_id' => $product->id,
            'store_id' => $otherStore->id,
            'quantity' => 100,
            'purchase_price' => 40,
        ]);

        $this->expectException(RuntimeException::class);

        try {
            $this->deduct($product, 1);
        } finally {
            $this->assertSame(100.0, (float) $elsewhere->fresh()->quantity);
        }
    }

    public function test_available_stock_is_summed_across_stores_but_filtered_per_store(): void
    {
        $product = $this->product();
        $otherStore = $this->store('Second warehouse');

        $this->batch($product, quantity: 30);

        Stock::create([
            'product_id' => $product->id,
            'store_id' => $otherStore->id,
            'quantity' => 12,
            'purchase_price' => 40,
        ]);

        $this->assertSame(42.0, $this->stock->getAvailableStock($product->id));
        $this->assertSame(30.0, $this->stock->getAvailableStock($product->id, $this->store->id));
        $this->assertSame(12.0, $this->stock->getAvailableStock($product->id, $otherStore->id));
    }

    public function test_deduction_logs_the_quantity_before_and_after(): void
    {
        $product = $this->product();

        $this->batch($product, quantity: 20);
        $this->deduct($product, 8);

        $movement = StockMovement::sole();

        $this->assertSame(8.0, (float) $movement->quantity);
        $this->assertSame(20.0, (float) $movement->before_quantity);
        $this->assertSame(12.0, (float) $movement->after_quantity);
        $this->assertSame($this->user->id, $movement->created_by);
    }

    /**
     * The movement type is inferred from the reference class, and the stock
     * report groups on it — so a wrong guess misfiles the row.
     *
     * @param  class-string|null  $referenceType
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('movementTypes')]
    public function test_movement_type_is_inferred_from_the_reference(
        string $direction,
        ?string $referenceType,
        string $expected,
    ): void {
        $product = $this->product();

        if ($direction === 'in') {
            $this->stock->addStock(
                productId: $product->id,
                storeId: $this->store->id,
                quantity: 5,
                purchasePrice: 40,
                expiryDate: null,
                referenceType: $referenceType,
                referenceId: $referenceType ? 1 : null,
                createdBy: $this->user->id,
            );
        } else {
            $this->batch($product, quantity: 5);
            $this->deduct($product, 5, $referenceType);
        }

        $this->assertSame($expected, StockMovement::sole()->movement_type);
    }

    /**
     * @return array<string, array{0: string, 1: class-string|null, 2: string}>
     */
    public static function movementTypes(): array
    {
        return [
            'goods in with no reference' => ['in', null, 'adjustment'],
            'goods in from a purchase' => ['in', \App\Models\Purchase::class, 'purchase_in'],
            'goods in from a return' => ['in', \App\Models\SaleReturn::class, 'return_in'],
            'goods out with no reference' => ['out', null, 'adjustment'],
            'goods out for a sale' => ['out', Sale::class, 'sale_out'],
            'goods out for a purchase return' => ['out', PurchaseReturn::class, 'purchase_return_out'],
        ];
    }

    public function test_dropping_below_the_threshold_notifies_admins_and_store_managers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Role::create(['name' => 'Store Manager']);
        $storeManager = User::factory()->create();
        $storeManager->assignRole('Store Manager');

        $product = $this->product(threshold: 10);

        $this->batch($product, quantity: 12);
        $this->deduct($product, 5);

        Notification::assertSentTo($admin, LowStockNotification::class);
        Notification::assertSentTo($storeManager, LowStockNotification::class);
        Notification::assertNotSentTo($this->user, LowStockNotification::class);
    }

    public function test_staying_on_the_threshold_does_not_notify(): void
    {
        $product = $this->product(threshold: 10);

        $this->batch($product, quantity: 12);
        $this->deduct($product, 2);

        Notification::assertNothingSent();
    }

    public function test_a_product_without_a_threshold_never_notifies(): void
    {
        $product = $this->product(threshold: 0);

        $this->batch($product, quantity: 5);
        $this->deduct($product, 5);

        Notification::assertNothingSent();
    }

    private function deduct(Product $product, float $quantity, ?string $referenceType = null): void
    {
        $this->stock->deductStock(
            productId: $product->id,
            storeId: $this->store->id,
            quantity: $quantity,
            referenceType: $referenceType,
            referenceId: $referenceType ? 1 : null,
            createdBy: $this->user->id,
        );
    }

    private function batch(Product $product, float $quantity, ?\DateTimeInterface $createdAt = null): Stock
    {
        $batch = Stock::create([
            'product_id' => $product->id,
            'store_id' => $this->store->id,
            'quantity' => $quantity,
            'purchase_price' => 40,
        ]);

        if ($createdAt) {
            $batch->forceFill(['created_at' => $createdAt])->save();
        }

        return $batch;
    }

    private function product(float $threshold = 0): Product
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
            'min_stock_threshold' => $threshold,
            'status' => true,
        ]);
    }

    private function store(string $name = 'Main warehouse'): Store
    {
        return Store::create([
            'name' => $name,
            'location' => 'Dhaka',
            'status' => true,
        ]);
    }
}
