<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
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
 * A sale return credits the customer for goods coming back. The two things
 * worth guarding are the arithmetic — a returned unit must credit what the
 * customer actually paid, not the list price — and the ceiling, since the same
 * line can be returned across several visits.
 */
class SaleReturnFlowTest extends TestCase
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
    }

    public function test_a_restocking_return_puts_the_goods_back_and_refunds_the_customer(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 3, refund: 180))
            ->assertSessionHas('success');

        $return = SaleReturn::sole();

        $this->assertSame(180.0, (float) $return->total_amount);
        $this->assertSame(180.0, (float) $return->refund_amount);
        $this->assertSame(3.0, $this->availableStock(), 'the returned units should be back on the shelf');
        $this->assertSame(-180.0, app(CashBankService::class)->getCashBalance());
    }

    public function test_a_non_restocking_return_refunds_without_returning_goods_to_stock(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 3, refund: 180, restock: false))
            ->assertSessionHas('success');

        $this->assertSame(0.0, $this->availableStock());
        $this->assertSame(-180.0, app(CashBankService::class)->getCashBalance());
    }

    /**
     * Goods can come back without money going out — the value sits as a credit
     * on the customer's balance instead.
     */
    public function test_a_return_with_no_refund_moves_no_money(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 3, refund: 0))
            ->assertSessionHas('success');

        $return = SaleReturn::sole();

        $this->assertSame(180.0, (float) $return->total_amount);
        $this->assertSame(0.0, (float) $return->refund_amount);
        $this->assertSame(180.0, $return->credited_amount);
        $this->assertSame(0.0, app(CashBankService::class)->getCashBalance());
    }

    /**
     * A discounted line must credit the discounted price. Crediting the list
     * price would refund more than the customer ever paid.
     */
    public function test_a_discounted_line_credits_what_the_customer_actually_paid(): void
    {
        // 10 units at 60 = 600, less a 100 discount = 500, so 50 per unit.
        $sale = $this->sale(discount: 100);

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 2, refund: 0))
            ->assertSessionHas('success');

        $this->assertSame(100.0, (float) SaleReturn::sole()->total_amount);
    }

    /**
     * VAT is excluded from the credit as well — the net line value is what gets
     * divided across the units.
     */
    public function test_vat_is_excluded_from_the_credited_value(): void
    {
        $sale = $this->sale(vatPercentage: 5);

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 2, refund: 0))
            ->assertSessionHas('success');

        $this->assertSame(120.0, (float) SaleReturn::sole()->total_amount);
    }

    /**
     * The flip side of the goods-only case: money leaving the till still has to
     * say which till it left.
     */
    public function test_a_refund_that_pays_out_still_needs_a_method(): void
    {
        $sale = $this->sale();

        $payload = $this->payload($sale, quantity: 3, refund: 180);
        $payload['refund_method'] = null;

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $payload)
            ->assertSessionHasErrors('refund_method');

        $this->assertSame(0, SaleReturn::count());
        $this->assertSame(0.0, app(CashBankService::class)->getCashBalance());
    }

    public function test_a_refund_cannot_exceed_the_value_of_the_returned_goods(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 2, refund: 500))
            ->assertSessionHas('error');

        $this->assertSame(0, SaleReturn::count());
        $this->assertSame(0.0, $this->availableStock());
        $this->assertSame(0.0, app(CashBankService::class)->getCashBalance());
    }

    public function test_more_than_was_sold_cannot_be_returned(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 11, refund: 0))
            ->assertSessionHas('error');

        $this->assertSame(0, SaleReturn::count());
    }

    /**
     * The ceiling has to account for what earlier returns already took back.
     */
    public function test_repeat_returns_cannot_exceed_the_quantity_sold(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 6, refund: 0))
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 5, refund: 0))
            ->assertSessionHas('error');

        $this->assertSame(1, SaleReturn::count());
        $this->assertSame(6.0, $this->availableStock(), 'only the first return should have restocked');

        // The remaining 4 are still returnable.
        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 4, refund: 0))
            ->assertSessionHas('success');

        $this->assertSame(10.0, $this->availableStock());
    }

    public function test_an_item_from_another_sale_is_rejected(): void
    {
        $sale = $this->sale();
        $otherSale = $this->sale();

        $payload = $this->payload($sale, quantity: 1, refund: 0);
        $payload['items'][0]['sale_item_id'] = $otherSale->items()->sole()->id;

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $payload)
            ->assertSessionHas('error');

        $this->assertSame(0, SaleReturn::count());
    }

    public function test_a_return_with_every_quantity_blank_is_rejected(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 0, refund: 0))
            ->assertSessionHas('error');

        $this->assertSame(0, SaleReturn::count());
    }

    public function test_a_return_cannot_be_dated_in_the_future(): void
    {
        $sale = $this->sale();

        $payload = $this->payload($sale, quantity: 1, refund: 0);
        $payload['return_date'] = now()->addDay()->toDateString();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $payload)
            ->assertSessionHasErrors('return_date');
    }

    public function test_deleting_a_return_takes_the_goods_back_out_and_reverses_the_refund(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 3, refund: 180));

        $return = SaleReturn::sole();

        $this->actingAs($this->admin)
            ->delete(route('admin.sale-returns.destroy', $return))
            ->assertRedirect(route('admin.sale-returns.index'));

        $this->assertSame(0.0, $this->availableStock(), 'the restocked goods should be pulled back out');
        $this->assertSame(0.0, app(CashBankService::class)->getCashBalance(), 'the refund should be reversed');
        $this->assertSoftDeleted($return);
    }

    public function test_only_an_administrator_can_delete_a_return(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $sale), $this->payload($sale, quantity: 3, refund: 180));

        $seller = User::factory()->create(['status' => true]);
        $seller->assignRole('Employee');

        $this->actingAs($seller)
            ->delete(route('admin.sale-returns.destroy', SaleReturn::sole()))
            ->assertForbidden();

        $this->assertSame(1, SaleReturn::count());
        $this->assertSame(3.0, $this->availableStock());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Sale $sale, float $quantity, float $refund, bool $restock = true): array
    {
        return [
            'return_date' => now()->toDateString(),
            'reason' => 'Customer changed their mind',
            'restock' => $restock,
            'refund_amount' => $refund,
            'refund_method' => $refund > 0 ? 'cash' : null,
            'items' => [[
                'sale_item_id' => $sale->items()->sole()->id,
                'quantity' => $quantity,
            ]],
        ];
    }

    private function availableStock(): float
    {
        return app(StockService::class)->getAvailableStock($this->product->id, $this->store->id);
    }

    /**
     * A sale of 10 units at 60, already deducted from stock.
     */
    private function sale(float $discount = 0, float $vatPercentage = 0): Sale
    {
        $vatAmount = 600 * $vatPercentage / 100;

        $sale = Sale::create([
            'customer_type' => 'client_agent',
            'customer_id' => $this->client->id,
            'sale_date' => now()->toDateString(),
            'subtotal' => 600,
            'discount_amount' => $discount,
            'vat_amount' => $vatAmount,
            'total_amount' => 600 - $discount + $vatAmount,
            'paid_amount' => 600 - $discount + $vatAmount,
            'payment_method' => 'cash',
            'created_by' => $this->admin->id,
        ]);

        $sale->items()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'quantity' => 10,
            'unit_price' => 60,
            'discount_amount' => $discount,
            'vat_percentage' => $vatPercentage,
            'vat_amount' => $vatAmount,
            'total_price' => 600 - $discount + $vatAmount,
        ]);

        return $sale;
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
