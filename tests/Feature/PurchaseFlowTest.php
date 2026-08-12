<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\CashBankService;
use App\Services\StockService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The mirror image of the sale flow: goods in, money out, and a payable owed to
 * the supplier. A purchase is also where a stock batch gets its cost price and
 * expiry date, which the FIFO deduction and the expiry reports then rely on.
 */
class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    private Store $store;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['is_admin' => true, 'status' => true]);
        $this->admin->assignRole('Admin');

        $this->store = Store::create(['name' => 'Main warehouse', 'location' => 'Dhaka', 'status' => true]);
        $this->product = $this->product();
        $this->supplier = $this->supplier();
    }

    public function test_creating_a_purchase_adds_stock_and_debits_the_cash_ledger(): void
    {
        $this->openingCash(10000);

        $response = $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 2000,
        ]));

        $response->assertRedirect(route('admin.purchases.index'))->assertSessionHasNoErrors();

        $purchase = Purchase::sole();

        $this->assertSame(2000.0, (float) $purchase->total_amount);
        $this->assertSame(50.0, $this->availableStock());
        $this->assertSame(8000.0, app(CashBankService::class)->getCashBalance());
    }

    /**
     * The batch carries the cost and expiry from the purchase line — the FIFO
     * deduction and the expiry reports read them straight off it.
     */
    public function test_the_new_batch_carries_the_cost_price_and_expiry_date(): void
    {
        $expiry = now()->addMonths(6)->toDateString();

        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 50,
                'purchase_price' => 40,
                'expiry_date' => $expiry,
            ]],
            'paid_amount' => 0,
        ]))->assertSessionHasNoErrors();

        $batch = Stock::sole();

        $this->assertSame(50.0, (float) $batch->quantity);
        $this->assertSame(40.0, (float) $batch->purchase_price);
        $this->assertSame($expiry, $batch->expiry_date->toDateString());
    }

    public function test_totals_are_derived_from_the_line_items(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 50,
                'purchase_price' => 40,
                'vat_percentage' => 5,
            ]],
            'paid_amount' => 0,
        ]))->assertSessionHasNoErrors();

        $purchase = Purchase::sole();

        $this->assertSame(2000.0, (float) $purchase->subtotal);
        $this->assertSame(100.0, (float) $purchase->vat_amount);
        $this->assertSame(2100.0, (float) $purchase->total_amount);
    }

    public function test_a_partly_paid_purchase_records_a_payable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 500,
        ]))->assertSessionHasNoErrors();

        $purchase = Purchase::sole();

        $this->assertSame(1500.0, (float) $purchase->due_amount);
        $this->assertSame('partial', $purchase->payment_status);

        $payable = Account::sole();
        $this->assertSame('payable', $payable->type);
        $this->assertSame('supplier', $payable->party_type);
        $this->assertSame($this->supplier->id, $payable->party_id);
        $this->assertSame(1500.0, (float) $payable->amount);
    }

    public function test_a_fully_paid_purchase_records_no_payable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 2000,
        ]))->assertSessionHasNoErrors();

        $this->assertSame('paid', Purchase::sole()->payment_status);
        $this->assertSame(0, Account::count());
    }

    public function test_deleting_a_purchase_removes_the_stock_refunds_the_payment_and_clears_the_payable(): void
    {
        $this->openingCash(10000);

        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 500,
        ]))->assertSessionHasNoErrors();

        $purchase = Purchase::sole();
        $this->assertSame(1, Account::count());

        $this->actingAs($this->admin)
            ->delete(route('admin.purchases.destroy', $purchase))
            ->assertRedirect(route('admin.purchases.index'));

        $this->assertSame(0.0, $this->availableStock(), 'the goods should be back off the shelf');
        $this->assertSame(10000.0, app(CashBankService::class)->getCashBalance(), 'the payment should be reversed');
        $this->assertSame(0, Account::count());
        $this->assertSoftDeleted($purchase);
    }

    /**
     * Deleting a purchase pulls its goods back out of stock. If they have
     * already been sold there is nothing to pull, so the deletion has to fail
     * rather than drive the batch negative.
     */
    public function test_a_purchase_cannot_be_deleted_once_its_goods_have_been_sold(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 0,
        ]))->assertSessionHasNoErrors();

        $purchase = Purchase::sole();

        app(StockService::class)->deductStock(
            productId: $this->product->id,
            storeId: $this->store->id,
            quantity: 50,
            referenceType: null,
            referenceId: null,
            createdBy: $this->admin->id,
        );

        $this->expectException(\RuntimeException::class);

        try {
            $this->withoutExceptionHandling()
                ->actingAs($this->admin)
                ->delete(route('admin.purchases.destroy', $purchase));
        } finally {
            $this->assertNotSoftDeleted($purchase);
            $this->assertSame(0.0, $this->availableStock(), 'the failed deletion must not drive stock negative');
        }
    }

    public function test_a_purchase_with_returns_cannot_be_edited_or_deleted(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 0,
        ]))->assertSessionHasNoErrors();

        $purchase = Purchase::sole();

        PurchaseReturn::create([
            'purchase_id' => $purchase->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged carton',
            'total_amount' => 200,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.purchases.edit', $purchase))
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHas('error');

        $this->actingAs($this->admin)
            ->delete(route('admin.purchases.destroy', $purchase))
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($purchase);
        $this->assertSame(50.0, $this->availableStock());
    }

    public function test_updating_a_purchase_reverses_the_original_stock_and_payment(): void
    {
        $this->openingCash(10000);

        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 2000,
        ]))->assertSessionHasNoErrors();

        $purchase = Purchase::sole();

        $this->actingAs($this->admin)->put(route('admin.purchases.update', $purchase), $this->payload([
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 20,
                'purchase_price' => 40,
            ]],
            'paid_amount' => 800,
        ]))->assertSessionHasNoErrors();

        $purchase->refresh();

        $this->assertSame(800.0, (float) $purchase->total_amount);
        $this->assertSame(20.0, $this->availableStock(), 'only the revised 20 units should be on hand');
        $this->assertSame(9200.0, app(CashBankService::class)->getCashBalance());
        $this->assertSame(1, $purchase->items()->count(), 'the original line must be replaced, not duplicated');
    }

    public function test_a_user_without_purchase_permission_cannot_create_one(): void
    {
        $seller = User::factory()->create(['status' => true]);
        $seller->assignRole('Employee');

        $this->actingAs($seller)
            ->post(route('admin.purchases.store'), $this->payload(['paid_amount' => 0]))
            ->assertForbidden();

        $this->assertSame(0, Purchase::count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 50,
                'purchase_price' => 40,
            ]],
        ], $overrides);
    }

    private function availableStock(): float
    {
        return app(StockService::class)->getAvailableStock($this->product->id, $this->store->id);
    }

    private function openingCash(float $amount): void
    {
        \App\Models\OpeningBalance::create([
            'method' => 'cash',
            'amount' => $amount,
            'date' => now()->toDateString(),
            'set_by' => $this->admin->id,
        ]);
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

    private function supplier(): Supplier
    {
        return Supplier::create([
            'name' => 'Acme Supplies',
            'email' => 'acme@example.com',
            'phone' => '01700000000',
            'address' => '1 Trade Road',
            'company_name' => 'Acme Ltd',
            'status' => true,
        ]);
    }
}
