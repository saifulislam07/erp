<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashBankTransaction;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
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
 * Storing a sale has to move three things at once: stock out of a batch, money
 * into the cash ledger, and a receivable onto the client's account. CHECKLIST.md
 * calls `paid_amount` the single source of truth "so the invoice screens, the
 * reports and the ledgers cannot disagree" — this is where that is enforced.
 */
class SaleFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    private Store $store;

    private Client $client;

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

        $this->stockUp(100);
    }

    public function test_creating_a_sale_deducts_stock_and_credits_the_cash_ledger(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'paid_amount' => 600,
        ]));

        $response->assertRedirect(route('admin.sales.index'))->assertSessionHasNoErrors();

        $sale = Sale::sole();

        $this->assertSame(600.0, (float) $sale->total_amount);
        $this->assertSame(90.0, $this->availableStock(), '10 units should have left the batch');
        $this->assertSame(600.0, app(CashBankService::class)->getCashBalance());

        $item = $sale->items()->sole();
        $this->assertSame(10.0, (float) $item->quantity);
        $this->assertSame(600.0, (float) $item->total_price);
    }

    /**
     * subtotal − discount + VAT. The line VAT is charged on the undiscounted
     * price, which is what the create form shows.
     */
    public function test_totals_are_derived_from_the_line_items(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 10,
                'unit_price' => 60,
                'discount_amount' => 50,
                'vat_percentage' => 5,
            ]],
            'paid_amount' => 0,
        ]))->assertSessionHasNoErrors();

        $sale = Sale::sole();

        $this->assertSame(600.0, (float) $sale->subtotal);
        $this->assertSame(50.0, (float) $sale->discount_amount);
        $this->assertSame(30.0, (float) $sale->vat_amount);
        $this->assertSame(580.0, (float) $sale->total_amount);
    }

    public function test_a_partly_paid_sale_to_a_client_records_a_receivable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'paid_amount' => 250,
        ]))->assertSessionHasNoErrors();

        $sale = Sale::sole();

        $this->assertSame(350.0, (float) $sale->due_amount);
        $this->assertSame('partial', $sale->payment_status);

        $receivable = Account::sole();
        $this->assertSame('receivable', $receivable->type);
        $this->assertSame('client', $receivable->party_type);
        $this->assertSame($this->client->id, $receivable->party_id);
        $this->assertSame(350.0, (float) $receivable->amount);
        $this->assertFalse($receivable->is_settled);
    }

    public function test_a_fully_paid_sale_records_no_receivable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'paid_amount' => 600,
        ]))->assertSessionHasNoErrors();

        $this->assertSame('paid', Sale::sole()->payment_status);
        $this->assertSame(0, Account::count());
    }

    /**
     * A walk-in customer has no account to bill later, so an unpaid local sale
     * must not leave a receivable pointing at nobody.
     */
    public function test_a_local_sale_never_records_a_receivable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'customer_type' => 'local',
            'customer_id' => null,
            'customer_name' => 'Walk-in customer',
            'paid_amount' => 0,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(600.0, (float) Sale::sole()->due_amount);
        $this->assertSame(0, Account::count());
    }

    public function test_selling_more_than_is_in_stock_is_rejected_and_writes_nothing(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 101,
                'unit_price' => 60,
            ]],
            'paid_amount' => 0,
        ]));

        $response->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, Sale::count());
        $this->assertSame(100.0, $this->availableStock(), 'a rejected sale must not touch stock');
        $this->assertSame(0, CashBankTransaction::count());
    }

    /**
     * `sale.discount` gates the per-line discount box; a seller without it can
     * still post the form, so the server zeroes the field rather than trusting it.
     */
    public function test_a_seller_without_discount_permission_has_the_discount_zeroed(): void
    {
        $seller = User::factory()->create(['status' => true]);
        $seller->assignRole('Employee');

        $this->actingAs($seller)->post(route('admin.sales.store'), $this->payload([
            'customer_type' => 'local',
            'customer_id' => null,
            'customer_name' => 'Walk-in customer',
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 10,
                'unit_price' => 60,
                'discount_amount' => 500,
            ]],
            'paid_amount' => 0,
        ]))->assertSessionHasNoErrors();

        $sale = Sale::sole();

        $this->assertSame(0.0, (float) $sale->discount_amount);
        $this->assertSame(600.0, (float) $sale->total_amount, 'the discount must not reach the total');
    }

    public function test_a_seller_without_client_agent_permission_cannot_sell_to_a_client(): void
    {
        $seller = User::factory()->create(['status' => true]);
        $seller->assignRole('Employee');

        $this->actingAs($seller)
            ->post(route('admin.sales.store'), $this->payload(['paid_amount' => 0]))
            ->assertSessionHasErrors('customer_type');

        $this->assertSame(0, Sale::count());
    }

    public function test_deleting_a_sale_restores_stock_reverses_payment_and_clears_the_receivable(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'paid_amount' => 250,
        ]))->assertSessionHasNoErrors();

        $sale = Sale::sole();
        $this->assertSame(1, Account::count());

        $this->actingAs($this->admin)
            ->delete(route('admin.sales.destroy', $sale))
            ->assertRedirect(route('admin.sales.index'));

        $this->assertSame(100.0, $this->availableStock(), 'the goods should be back in stock');
        $this->assertSame(0.0, app(CashBankService::class)->getCashBalance(), 'the payment should be reversed');
        $this->assertSame(0, Account::count());
        $this->assertSoftDeleted($sale);
    }

    /**
     * An edit is a full reversal followed by a re-post, so the net position has
     * to match a sale that had been entered correctly the first time.
     */
    public function test_updating_a_sale_reverses_the_original_stock_and_payment(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sales.store'), $this->payload([
            'paid_amount' => 600,
        ]))->assertSessionHasNoErrors();

        $sale = Sale::sole();

        $this->actingAs($this->admin)->put(route('admin.sales.update', $sale), $this->payload([
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 4,
                'unit_price' => 60,
            ]],
            'paid_amount' => 240,
        ]))->assertSessionHasNoErrors();

        $sale->refresh();

        $this->assertSame(240.0, (float) $sale->total_amount);
        $this->assertSame(96.0, $this->availableStock(), 'only the revised 4 units should be out');
        $this->assertSame(240.0, app(CashBankService::class)->getCashBalance());
        $this->assertSame(1, $sale->items()->count(), 'the original line must be replaced, not duplicated');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_type' => 'client_agent',
            'customer_id' => $this->client->id,
            'sale_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'items' => [[
                'product_id' => $this->product->id,
                'store_id' => $this->store->id,
                'quantity' => 10,
                'unit_price' => 60,
            ]],
        ], $overrides);
    }

    private function availableStock(): float
    {
        return app(StockService::class)->getAvailableStock($this->product->id, $this->store->id);
    }

    private function stockUp(float $quantity): Stock
    {
        return Stock::create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'quantity' => $quantity,
            'purchase_price' => 40,
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
