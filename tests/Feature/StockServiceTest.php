<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

/**
 * Stock is stored as immutable batches; the balance for a product is the sum
 * of its batch rows. These tests pin down the two things every other module
 * depends on: batches are consumed oldest-first, and a deduction that cannot
 * be covered is refused rather than driving the balance negative.
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

        $this->stock = app(StockService::class);
        $this->user = User::factory()->create();
        $this->store = Store::factory()->create();
    }

    public function test_adding_stock_creates_a_batch_and_records_the_movement(): void
    {
        $product = Product::factory()->create();

        $this->stock->addStock(
            productId: $product->id,
            storeId: $this->store->id,
            quantity: 50,
            purchasePrice: 80,
            expiryDate: null,
            referenceType: null,
            referenceId: null,
            createdBy: $this->user->id,
        );

        $this->assertSame(50.0, $this->stock->getAvailableStock($product->id));

        $movement = StockMovement::sole();
        $this->assertSame('adjustment', $movement->movement_type);
        $this->assertSame('0.00', $movement->before_quantity);
        $this->assertSame('50.00', $movement->after_quantity);
    }

    public function test_stock_is_deducted_from_the_oldest_batch_first(): void
    {
        $product = Product::factory()->create();

        $older = $this->addBatch($product, quantity: 10, price: 70, at: now()->subDays(5));
        $newer = $this->addBatch($product, quantity: 10, price: 90, at: now()->subDay());

        $this->stock->deductStock(
            productId: $product->id,
            storeId: $this->store->id,
            quantity: 15,
            referenceType: null,
            referenceId: null,
            createdBy: $this->user->id,
        );

        // The first batch is emptied before the second one is touched.
        $this->assertSame('0.00', $older->fresh()->quantity);
        $this->assertSame('5.00', $newer->fresh()->quantity);
        $this->assertSame(5.0, $this->stock->getAvailableStock($product->id));
    }

    public function test_deducting_more_than_is_available_is_refused(): void
    {
        $product = Product::factory()->create();
        $this->addBatch($product, quantity: 3);

        $this->expectException(RuntimeException::class);

        try {
            $this->stock->deductStock(
                productId: $product->id,
                storeId: $this->store->id,
                quantity: 4,
                referenceType: null,
                referenceId: null,
                createdBy: $this->user->id,
            );
        } finally {
            // Nothing is half-applied: the batch and the ledger are untouched.
            $this->assertSame(3.0, $this->stock->getAvailableStock($product->id));
            $this->assertSame(1, StockMovement::count());
        }
    }

    public function test_stock_is_counted_per_store(): void
    {
        $product = Product::factory()->create();
        $other = Store::factory()->create();

        $this->addBatch($product, quantity: 8);
        $this->addBatch($product, quantity: 5, store: $other);

        $this->assertSame(8.0, $this->stock->getAvailableStock($product->id, $this->store->id));
        $this->assertSame(5.0, $this->stock->getAvailableStock($product->id, $other->id));
        $this->assertSame(13.0, $this->stock->getAvailableStock($product->id));
    }

    public function test_dropping_below_the_threshold_warns_the_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $product = Product::factory()->lowStockBelow(10)->create();

        $this->addBatch($product, quantity: 12);

        $this->stock->deductStock(
            productId: $product->id,
            storeId: $this->store->id,
            quantity: 5,
            referenceType: null,
            referenceId: null,
            createdBy: $this->user->id,
        );

        Notification::assertSentTo($admin, LowStockNotification::class);
    }

    public function test_staying_above_the_threshold_stays_quiet(): void
    {
        Notification::fake();

        User::factory()->admin()->create();
        $product = Product::factory()->lowStockBelow(10)->create();

        $this->addBatch($product, quantity: 30);

        $this->stock->deductStock(
            productId: $product->id,
            storeId: $this->store->id,
            quantity: 5,
            referenceType: null,
            referenceId: null,
            createdBy: $this->user->id,
        );

        Notification::assertNothingSent();
    }

    /**
     * Batch rows are consumed in creation order, so tests that care about FIFO
     * need to control `created_at` rather than relying on insert speed.
     */
    private function addBatch(Product $product, float $quantity, float $price = 80, ?Store $store = null, $at = null): Stock
    {
        $stock = $this->stock->addStock(
            productId: $product->id,
            storeId: ($store ?? $this->store)->id,
            quantity: $quantity,
            purchasePrice: $price,
            expiryDate: null,
            referenceType: null,
            referenceId: null,
            createdBy: $this->user->id,
        );

        if ($at) {
            $stock->forceFill(['created_at' => $at])->saveQuietly();
        }

        return $stock;
    }
}
