<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\Store;
use App\Models\User;
use App\Services\CashBankService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sale returns run the sale backwards: goods come back into a store, money
 * goes back out of the till. The two rules worth guarding are that a line
 * cannot be returned twice over, and that the refund is capped at what the
 * returned goods are actually worth — a return must never be a way to hand
 * out more cash than the customer paid.
 */
class SaleReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Store $store;

    private Product $product;

    private Sale $sale;

    private SaleItem $saleItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->store = Store::factory()->create();
        $this->product = Product::factory()->create();

        // 10 units sold at 100 each, no discount and no VAT.
        $this->sale = Sale::create([
            'customer_type' => 'local',
            'customer_name' => 'Walk-in customer',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 1000,
            'payment_method' => 'cash',
            'paid_amount' => 1000,
            'created_by' => $this->admin->id,
        ]);

        $this->saleItem = $this->sale->items()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'quantity' => 10,
            'unit_price' => 100,
            'discount_amount' => 0,
            'vat_percentage' => 0,
            'vat_amount' => 0,
            'total_price' => 1000,
        ]);
    }

    public function test_returning_goods_puts_them_back_into_stock(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $this->sale), $this->payload())
            ->assertSessionHasNoErrors();

        $return = SaleReturn::sole();

        $this->assertSame('300.00', $return->total_amount);
        $this->assertSame(3.0, app(StockService::class)->getAvailableStock($this->product->id, $this->store->id));
    }

    public function test_a_return_without_restocking_leaves_stock_alone(): void
    {
        // Damaged goods come back on paper only.
        $this->actingAs($this->admin)->post(route('admin.sale-returns.store', $this->sale), $this->payload([
            'restock' => 0,
        ]));

        $this->assertFalse(SaleReturn::sole()->restock);
        $this->assertSame(0.0, app(StockService::class)->getAvailableStock($this->product->id));
    }

    /**
     * Regression: `required_with:refund_amount` demanded a refund method even
     * for a refund of zero, because the request normalises a blank box to 0
     * and 0 counts as "present". That made every refund-free return — an
     * exchange, a credit note, goods back from an unpaid invoice —
     * impossible to record.
     */
    public function test_a_return_with_no_refund_needs_no_refund_method(): void
    {
        foreach ([[], ['refund_amount' => ''], ['refund_amount' => 0]] as $variant) {
            $payload = $this->payload($variant);
            unset($payload['refund_method']);

            if ($variant === []) {
                unset($payload['refund_amount']);
            }

            $this->actingAs($this->admin)
                ->post(route('admin.sale-returns.store', $this->sale), $payload)
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(3, SaleReturn::count());
    }

    public function test_a_refund_takes_money_back_out_of_the_till(): void
    {
        $before = app(CashBankService::class)->getCashBalance();

        $this->actingAs($this->admin)->post(route('admin.sale-returns.store', $this->sale), $this->payload([
            'refund_amount' => 300,
            'refund_method' => 'cash',
        ]));

        $this->assertSame('300.00', SaleReturn::sole()->refund_amount);
        $this->assertSame($before - 300, app(CashBankService::class)->getCashBalance());
    }

    public function test_a_refund_larger_than_the_returned_goods_is_refused(): void
    {
        $cashBefore = app(CashBankService::class)->getCashBalance();

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $this->sale), $this->payload([
                'refund_amount' => 500,
                'refund_method' => 'cash',
            ]))
            ->assertSessionHas('error');

        $this->assertSame(0, SaleReturn::count());
        $this->assertSame($cashBefore, app(CashBankService::class)->getCashBalance());
        $this->assertSame(0.0, app(StockService::class)->getAvailableStock($this->product->id));
    }

    public function test_the_same_units_cannot_be_returned_twice(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sale-returns.store', $this->sale), $this->payload([
            'items' => [$this->item(['quantity' => 8])],
        ]));

        // Only 2 of the 10 remain returnable.
        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $this->sale), $this->payload([
                'items' => [$this->item(['quantity' => 3])],
            ]))
            ->assertSessionHas('error');

        $this->assertSame(1, SaleReturn::count());
        $this->assertSame(8.0, app(StockService::class)->getAvailableStock($this->product->id));
    }

    public function test_a_line_from_another_sale_is_rejected(): void
    {
        $otherSale = Sale::create([
            'customer_type' => 'local',
            'customer_name' => 'Someone else',
            'sale_date' => now()->toDateString(),
            'subtotal' => 100,
            'total_amount' => 100,
            'payment_method' => 'cash',
            'paid_amount' => 100,
            'created_by' => $this->admin->id,
        ]);

        $foreignItem = $otherSale->items()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $this->sale), $this->payload([
                'items' => [['sale_item_id' => $foreignItem->id, 'quantity' => 1]],
            ]))
            ->assertSessionHas('error');

        $this->assertSame(0, SaleReturn::count());
    }

    public function test_a_return_cannot_be_dated_in_the_future(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $this->sale), $this->payload([
                'return_date' => now()->addWeek()->toDateString(),
            ]))
            ->assertSessionHasErrors('return_date');
    }

    public function test_a_return_with_every_quantity_left_blank_is_refused(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $this->sale), $this->payload([
                'items' => [$this->item(['quantity' => 0])],
            ]))
            ->assertSessionHas('error');

        $this->assertSame(0, SaleReturn::count());
    }

    public function test_deleting_a_return_reverses_the_stock_and_the_refund(): void
    {
        $cashBefore = app(CashBankService::class)->getCashBalance();

        $this->actingAs($this->admin)->post(route('admin.sale-returns.store', $this->sale), $this->payload([
            'refund_amount' => 300,
            'refund_method' => 'cash',
        ]));

        $return = SaleReturn::sole();

        $this->actingAs($this->admin)
            ->delete(route('admin.sale-returns.destroy', $return))
            ->assertRedirect(route('admin.sale-returns.index'));

        $this->assertSoftDeleted($return);
        $this->assertSame(0.0, app(StockService::class)->getAvailableStock($this->product->id));
        $this->assertSame($cashBefore, app(CashBankService::class)->getCashBalance());
    }

    public function test_deleting_a_return_frees_the_units_to_be_returned_again(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sale-returns.store', $this->sale), $this->payload([
            'items' => [$this->item(['quantity' => 10])],
        ]));

        $this->actingAs($this->admin)->delete(route('admin.sale-returns.destroy', SaleReturn::sole()));

        $this->actingAs($this->admin)
            ->post(route('admin.sale-returns.store', $this->sale), $this->payload([
                'items' => [$this->item(['quantity' => 10])],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, SaleReturn::count());
    }

    public function test_only_an_admin_may_delete_a_return(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sale-returns.store', $this->sale), $this->payload());
        $return = SaleReturn::sole();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.sale-returns.destroy', $return))
            ->assertForbidden();

        $this->assertNotSoftDeleted($return);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'return_date' => now()->toDateString(),
            'reason' => 'Customer changed their mind',
            'restock' => 1,
            'refund_amount' => 0,
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
            'sale_item_id' => $this->saleItem->id,
            'quantity' => 3,
        ], $overrides);
    }
}
