<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Sending goods back to a supplier. This is the mirror of a sale return:
 * stock leaves rather than arrives, and the quantity guard has to hold across
 * several partial returns of the same purchase line.
 */
class PurchaseReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Store $store;

    private Product $product;

    private Purchase $purchase;

    private PurchaseItem $purchaseItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->store = Store::factory()->create();
        $this->product = Product::factory()->create();

        // 20 units bought at 50 each, already on the shelf.
        $this->purchase = Purchase::create([
            'supplier_id' => Supplier::factory()->create()->id,
            'purchase_date' => now()->toDateString(),
            'subtotal' => 1000,
            'vat_amount' => 0,
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'payment_method' => 'cash',
            'created_by' => $this->admin->id,
        ]);

        $this->purchaseItem = $this->purchase->items()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'quantity' => 20,
            'purchase_price' => 50,
            'vat_percentage' => 0,
            'vat_amount' => 0,
            'total_price' => 1000,
        ]);

        app(StockService::class)->addStock(
            productId: $this->product->id,
            storeId: $this->store->id,
            quantity: 20,
            purchasePrice: 50,
            expiryDate: null,
            referenceType: null,
            referenceId: null,
            createdBy: $this->admin->id,
        );
    }

    public function test_returning_goods_to_the_supplier_takes_them_out_of_stock(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.purchases.returns.store', $this->purchase), $this->payload())
            ->assertRedirect(route('admin.purchases.returns.index', $this->purchase))
            ->assertSessionHasNoErrors();

        $return = PurchaseReturn::sole();

        // 5 units at the price they were bought for.
        $this->assertSame('250.00', $return->total_amount);
        $this->assertSame(15.0, app(StockService::class)->getAvailableStock($this->product->id, $this->store->id));
    }

    public function test_the_line_is_priced_at_what_was_paid_not_the_current_price(): void
    {
        $this->product->update(['purchase_price' => 999]);

        $this->actingAs($this->admin)->post(route('admin.purchases.returns.store', $this->purchase), $this->payload());

        $this->assertSame('50.00', PurchaseReturn::sole()->items()->sole()->unit_price);
    }

    public function test_more_than_was_bought_cannot_be_returned(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.purchases.returns.store', $this->purchase), $this->payload([
                'items' => [$this->item(['quantity' => 25])],
            ]))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, PurchaseReturn::count());
        $this->assertSame(20.0, app(StockService::class)->getAvailableStock($this->product->id));
    }

    public function test_partial_returns_add_up_against_the_same_line(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.returns.store', $this->purchase), $this->payload([
            'items' => [$this->item(['quantity' => 12])],
        ]));

        // 8 left; asking for 9 must be refused.
        $this->actingAs($this->admin)
            ->post(route('admin.purchases.returns.store', $this->purchase), $this->payload([
                'items' => [$this->item(['quantity' => 9])],
            ]))
            ->assertSessionHasErrors('items.0.quantity');

        $this->actingAs($this->admin)
            ->post(route('admin.purchases.returns.store', $this->purchase), $this->payload([
                'items' => [$this->item(['quantity' => 8])],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, PurchaseReturn::count());
        $this->assertSame(0.0, app(StockService::class)->getAvailableStock($this->product->id));
    }

    public function test_a_return_with_every_quantity_left_blank_is_refused(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.purchases.returns.store', $this->purchase), $this->payload([
                'items' => [$this->item(['quantity' => 0])],
            ]))
            ->assertSessionHasErrors('items');

        $this->assertSame(0, PurchaseReturn::count());
    }

    public function test_deleting_a_return_puts_the_stock_back(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.returns.store', $this->purchase), $this->payload());

        $return = PurchaseReturn::sole();

        $this->actingAs($this->admin)
            ->delete(route('admin.purchases.returns.destroy', [$this->purchase, $return]))
            ->assertRedirect(route('admin.purchases.returns.index', $this->purchase));

        $this->assertSoftDeleted($return);
        $this->assertSame(20.0, app(StockService::class)->getAvailableStock($this->product->id, $this->store->id));
    }

    /**
     * Regression: these routes sat outside every `check.permission` group and
     * the controller authorises nothing itself, so any signed-in user who knew
     * the URL could move stock by filing a return.
     */
    public function test_a_user_without_the_purchase_permission_is_refused(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.purchases.returns.store', $this->purchase), $this->payload())
            ->assertForbidden();

        $this->assertSame(0, PurchaseReturn::count());
        $this->assertSame(20.0, app(StockService::class)->getAvailableStock($this->product->id));
    }

    public function test_a_user_holding_the_purchase_permission_may_file_one(): void
    {
        $staff = User::factory()->create();
        Permission::findOrCreate('purchase.view', 'web');
        $staff->givePermissionTo('purchase.view');

        $this->actingAs($staff->fresh())
            ->post(route('admin.purchases.returns.store', $this->purchase), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertSame(1, PurchaseReturn::count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'return_date' => now()->toDateString(),
            'reason' => 'Wrong goods delivered',
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
            'purchase_item_id' => $this->purchaseItem->id,
            'quantity' => 5,
        ], $overrides);
    }
}
