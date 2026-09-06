<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CashBankService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Recording a purchase touches four things at once — the invoice, stock, the
 * cash/bank ledger and the supplier payable. These tests drive the controller
 * end to end so the four stay in step, including when a purchase is deleted
 * and everything has to be put back.
 */
class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Supplier $supplier;

    private Store $store;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supplier = Supplier::factory()->create();
        $this->store = Store::factory()->create();
        $this->product = Product::factory()->create();
    }

    public function test_a_purchase_records_the_invoice_and_raises_stock(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.purchases.store'), $this->payload())
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHasNoErrors();

        $purchase = Purchase::sole();

        $this->assertSame('800.00', $purchase->subtotal);
        $this->assertSame('800.00', $purchase->total_amount);
        $this->assertSame(1, $purchase->items()->count());

        $this->assertSame(10.0, app(StockService::class)->getAvailableStock($this->product->id, $this->store->id));
    }

    public function test_vat_is_added_on_top_of_the_line_total(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'items' => [$this->item(['vat_percentage' => 15])],
        ]));

        $purchase = Purchase::sole();

        // 10 x 80 = 800 net, 15% VAT = 120.
        $this->assertSame('800.00', $purchase->subtotal);
        $this->assertSame('120.00', $purchase->vat_amount);
        $this->assertSame('920.00', $purchase->total_amount);
    }

    public function test_an_unpaid_purchase_becomes_a_supplier_payable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 0,
        ]));

        $purchase = Purchase::sole();

        $this->assertSame('unpaid', $purchase->payment_status);
        $this->assertSame('800.00', $purchase->due_amount);

        $payable = Account::sole();
        $this->assertSame('payable', $payable->type);
        $this->assertSame('supplier', $payable->party_type);
        $this->assertSame($this->supplier->id, $payable->party_id);
        $this->assertSame('800.00', $payable->amount);
    }

    public function test_paying_in_full_leaves_no_payable_and_takes_the_money_from_cash(): void
    {
        $before = app(CashBankService::class)->getCashBalance();

        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 800,
            'payment_method' => 'cash',
        ]));

        $this->assertSame('paid', Purchase::sole()->payment_status);
        $this->assertSame(0, Account::count());
        $this->assertSame($before - 800, app(CashBankService::class)->getCashBalance());
    }

    public function test_a_part_payment_leaves_only_the_remainder_payable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 300,
        ]));

        $this->assertSame('partial', Purchase::sole()->payment_status);
        $this->assertSame('500.00', Account::sole()->amount);
    }

    public function test_deleting_a_purchase_reverses_the_stock_the_cash_and_the_payable(): void
    {
        $cashBefore = app(CashBankService::class)->getCashBalance();

        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload([
            'paid_amount' => 300,
        ]));

        $purchase = Purchase::sole();

        $this->actingAs($this->admin)
            ->delete(route('admin.purchases.destroy', $purchase))
            ->assertRedirect(route('admin.purchases.index'));

        $this->assertSoftDeleted($purchase);
        $this->assertSame(0.0, app(StockService::class)->getAvailableStock($this->product->id));
        $this->assertSame($cashBefore, app(CashBankService::class)->getCashBalance());
        $this->assertSame(0, Account::count());
    }

    public function test_a_purchase_with_returns_cannot_be_edited_or_deleted(): void
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), $this->payload());
        $purchase = Purchase::sole();

        PurchaseReturn::create([
            'purchase_id' => $purchase->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged carton',
            'total_amount' => 80,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.purchases.edit', $purchase))
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHas('error');

        $this->actingAs($this->admin)
            ->delete(route('admin.purchases.destroy', $purchase))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($purchase);
    }

    public function test_a_purchase_needs_at_least_one_item(): void
    {
        $payload = $this->payload();
        unset($payload['items']);

        $this->actingAs($this->admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Purchase::count());
    }

    public function test_a_user_without_the_purchase_permission_is_refused(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->post(route('admin.purchases.store'), $this->payload())
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
            'invoice_number' => 'INV-001',
            'payment_method' => 'cash',
            'paid_amount' => 0,
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
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'quantity' => 10,
            'purchase_price' => 80,
            'vat_percentage' => 0,
        ], $overrides);
    }
}
