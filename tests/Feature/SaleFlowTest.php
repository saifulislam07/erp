<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use App\Services\CashBankService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The sale counter: stock leaves, money arrives, and anything still owed by a
 * client/agent becomes a receivable. Two rules here are permission-shaped
 * rather than arithmetic — who may discount, and who may bill a client/agent
 * account — so both are pinned down alongside the maths.
 */
class SaleFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Store $store;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->store = Store::factory()->create();
        $this->product = Product::factory()->create();

        $this->stockOnHand(100);
    }

    public function test_a_cash_sale_deducts_stock_and_brings_the_money_in(): void
    {
        $cashBefore = app(CashBankService::class)->getCashBalance();

        $this->actingAs($this->admin)
            ->post(route('admin.sales.store'), $this->payload(['paid_amount' => 500]))
            ->assertRedirect(route('admin.sales.index'))
            ->assertSessionHasNoErrors();

        $sale = Sale::sole();

        // 5 x 100 = 500.
        $this->assertSame('500.00', $sale->subtotal);
        $this->assertSame('500.00', $sale->total_amount);
        $this->assertSame('paid', $sale->payment_status);

        $this->assertSame(95.0, app(StockService::class)->getAvailableStock($this->product->id, $this->store->id));
        $this->assertSame($cashBefore + 500, app(CashBankService::class)->getCashBalance());
    }

    public function test_discount_is_taken_off_and_vat_added_back_on(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'items' => [$this->item(['discount_amount' => 50, 'vat_percentage' => 10])],
        ]));

        $sale = Sale::sole();

        // 500 net − 50 discount + 50 VAT (10% of 500).
        $this->assertSame('500.00', $sale->subtotal);
        $this->assertSame('50.00', $sale->discount_amount);
        $this->assertSame('50.00', $sale->vat_amount);
        $this->assertSame('500.00', $sale->total_amount);
    }

    public function test_a_seller_without_the_discount_permission_gets_no_discount(): void
    {
        $seller = $this->sellerWith('sale.create');

        $this->actingAs($seller)->post(route('admin.sales.store'), $this->payload([
            'items' => [$this->item(['discount_amount' => 200])],
        ]))->assertSessionHasNoErrors();

        $sale = Sale::sole();

        // The discount is silently dropped rather than the sale being refused.
        $this->assertSame('0.00', $sale->discount_amount);
        $this->assertSame('500.00', $sale->total_amount);
    }

    public function test_a_seller_with_the_discount_permission_keeps_it(): void
    {
        $seller = $this->sellerWith('sale.create', 'sale.discount');

        $this->actingAs($seller)->post(route('admin.sales.store'), $this->payload([
            'items' => [$this->item(['discount_amount' => 200])],
        ]))->assertSessionHasNoErrors();

        $this->assertSame('200.00', Sale::sole()->discount_amount);
    }

    public function test_an_unpaid_client_sale_becomes_a_receivable(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'customer_type' => 'client_agent',
            'customer_id' => $client->id,
            'customer_name' => null,
            'paid_amount' => 200,
        ]));

        $sale = Sale::sole();
        $this->assertSame('partial', $sale->payment_status);

        $receivable = Account::sole();
        $this->assertSame('receivable', $receivable->type);
        $this->assertSame('client', $receivable->party_type);
        $this->assertSame($client->id, $receivable->party_id);
        $this->assertSame('300.00', $receivable->amount);
    }

    public function test_an_unpaid_walk_in_sale_creates_no_receivable(): void
    {
        // A local counter sale has no account to chase, so nothing is recorded.
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'paid_amount' => 0,
        ]));

        $this->assertSame('unpaid', Sale::sole()->payment_status);
        $this->assertSame(0, Account::count());
    }

    public function test_selling_more_than_is_in_stock_is_refused(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.sales.store'), $this->payload([
                'items' => [$this->item(['quantity' => 500])],
            ]))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, Sale::count());
        $this->assertSame(100.0, app(StockService::class)->getAvailableStock($this->product->id));
    }

    public function test_a_seller_without_the_client_agent_permission_cannot_bill_an_account(): void
    {
        $seller = $this->sellerWith('sale.create');
        $client = Client::factory()->create();

        $this->actingAs($seller)
            ->post(route('admin.sales.store'), $this->payload([
                'customer_type' => 'client_agent',
                'customer_id' => $client->id,
                'customer_name' => null,
            ]))
            ->assertSessionHasErrors('customer_type');

        $this->assertSame(0, Sale::count());
    }

    public function test_deleting_a_sale_puts_the_stock_back_and_returns_the_money(): void
    {
        $cashBefore = app(CashBankService::class)->getCashBalance();

        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'paid_amount' => 500,
        ]));

        $sale = Sale::sole();

        $this->actingAs($this->admin)
            ->delete(route('admin.sales.destroy', $sale))
            ->assertRedirect(route('admin.sales.index'));

        $this->assertSoftDeleted($sale);
        $this->assertSame(100.0, app(StockService::class)->getAvailableStock($this->product->id));
        $this->assertSame($cashBefore, app(CashBankService::class)->getCashBalance());
    }

    public function test_only_an_admin_may_delete_a_sale(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload());
        $sale = Sale::sole();

        $seller = $this->sellerWith('sale.create', 'sale.view');

        $this->actingAs($seller)
            ->delete(route('admin.sales.destroy', $sale))
            ->assertForbidden();

        $this->assertNotSoftDeleted($sale);
    }

    private function stockOnHand(float $quantity): void
    {
        app(StockService::class)->addStock(
            productId: $this->product->id,
            storeId: $this->store->id,
            quantity: $quantity,
            purchasePrice: 80,
            expiryDate: null,
            referenceType: null,
            referenceId: null,
            createdBy: $this->admin->id,
        );
    }

    /**
     * A non-admin sales user holding exactly the given permissions — the
     * `is_admin` flag would short circuit every check being tested here.
     */
    private function sellerWith(string ...$permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_type' => 'local',
            'customer_name' => 'Walk-in customer',
            'sale_date' => now()->toDateString(),
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
            'quantity' => 5,
            'unit_price' => 100,
            'discount_amount' => 0,
            'vat_percentage' => 0,
        ], $overrides);
    }
}
