<?php

namespace Tests\Feature;

use App\Models\CashBankTransaction;
use App\Models\Category;
use App\Models\Product;
use App\Models\Salary;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin listings answer their own URL twice: as HTML for the page and as a
 * yajra DataTables payload when the request is AJAX. This covers both halves so
 * a broken column name or a relation that cannot be ordered surfaces here
 * rather than as an empty table in the browser.
 */
class DataTableListingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['is_admin' => true, 'status' => true]);
        $this->admin->assignRole('Admin');
    }

    /**
     * @return array<int, array{0: string}>
     */
    public static function listingRoutes(): array
    {
        return [
            'products' => ['admin.products.index'],
            'clients' => ['admin.clients.index'],
            'suppliers' => ['admin.suppliers.index'],
            'sales' => ['admin.sales.index'],
            'purchases' => ['admin.purchases.index'],
            'orders' => ['admin.orders.index'],
            'stocks' => ['admin.stocks.index'],
            'expenses' => ['admin.expenses.index'],
            'assets' => ['admin.assets.index'],
            'employees' => ['admin.employees.index'],
            'categories' => ['admin.categories.index'],
            'departments' => ['admin.departments.index'],
            'units' => ['admin.units.index'],
            'stores' => ['admin.stores.index'],
            'roles' => ['admin.roles.index'],
            'feedbacks' => ['admin.feedbacks.index'],
            'salaries' => ['admin.salaries.index'],
            'activity log' => ['admin.activity-log.index'],
            'cash/bank transactions' => ['admin.cash-bank.transactions'],
            'order returns' => ['admin.returns.index'],
            'purchase returns' => ['admin.purchase-returns.index'],
            'sale returns' => ['admin.sale-returns.index'],
            'supplier balances' => ['admin.supplier-payments.index'],
            'supplier payments' => ['admin.supplier-payments.history'],
            'customer balances' => ['admin.customer-payments.index'],
            'customer payments' => ['admin.customer-payments.history'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listingRoutes')]
    public function test_listing_page_renders(string $routeName): void
    {
        $this->actingAs($this->admin)
            ->get(route($routeName))
            ->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listingRoutes')]
    public function test_listing_returns_a_datatables_payload(string $routeName): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route($routeName, [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    /**
     * The search closures build raw `table.column` conditions, so a wrong table
     * name only blows up once a term is actually typed. This drives that path on
     * every listing.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listingRoutes')]
    public function test_listing_accepts_a_search_term(string $routeName): void
    {
        $this->actingAs($this->admin)
            ->getJson(route($routeName, [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'q' => 'abc',
                'search' => ['value' => 'abc', 'regex' => 'false'],
            ]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    /**
     * Renders every cell callback for a real row — the column definitions and
     * the Blade partials only get exercised when the payload is not empty.
     */
    public function test_product_listing_renders_a_row(): void
    {
        $category = Category::create(['name' => 'Beverages', 'status' => true]);
        $unit = Unit::create(['name' => 'Piece']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Mango Juice',
            'unit_id' => $unit->id,
            'mrp_price' => 120,
            'purchase_price' => 80,
            'sale_price' => 100,
            'vat_percentage' => 0,
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.products.index', ['draw' => 1, 'start' => 0, 'length' => 10]),
                ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.unit_name', 'Piece');

        $row = $response->json('data.0');

        $this->assertStringContainsString('Mango Juice', $row['product']);
        $this->assertStringContainsString($product->unique_id, $row['product']);
        $this->assertStringContainsString('Beverages', $row['category_name']);
        $this->assertStringContainsString('Active', $row['state']);
        $this->assertStringContainsString(route('admin.products.edit', $product), $row['actions']);
    }

    /**
     * The listings whose row partials reach through relations: a null relation or
     * a mistyped accessor only shows up once a real row is rendered.
     */
    public function test_relation_backed_listings_render_a_row(): void
    {
        $store = Store::create(['name' => 'Main', 'location' => 'Dhaka', 'status' => true]);
        $category = Category::create(['name' => 'Dairy', 'status' => true]);
        $unit = Unit::create(['name' => 'Litre']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Full Cream Milk',
            'unit_id' => $unit->id,
            'mrp_price' => 100,
            'purchase_price' => 70,
            'sale_price' => 90,
            'status' => true,
        ]);

        Stock::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'quantity' => 12,
            'purchase_price' => 70,
            'expiry_date' => now()->addDays(10),
        ]);

        $sale = Sale::create([
            'sale_id' => 'SL2608001',
            'customer_type' => 'local',
            'customer_name' => 'Walk-in Rahim',
            'sale_date' => now(),
            'subtotal' => 90,
            'total_amount' => 90,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'paid_amount' => 90,
            'due_amount' => 0,
            'created_by' => $this->admin->id,
        ]);

        Salary::create([
            'user_id' => $this->admin->id,
            'month' => '2026-08',
            'basic_salary' => 30000,
            'deduction' => 0,
            'net_salary' => 30000,
            'payment_method' => 'cash',
            'paid_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        CashBankTransaction::create([
            'transaction_type' => 'credit',
            'method' => 'cash',
            'amount' => 90,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'description' => 'Sale SL2608001',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
            'balance_cash_after' => 90,
            'balance_bank_after' => 0,
        ]);

        $expectations = [
            // yajra escapes non-raw columns, so the "<" arrives as an entity.
            'admin.stocks.index' => ['product_label' => 'Full Cream Milk', 'expiry_label' => 'Expires &lt; 1 month'],
            'admin.sales.index' => ['customer_label' => 'Walk-in Rahim', 'state' => 'Paid'],
            'admin.salaries.index' => ['employee_name' => $this->admin->name, 'payment_method' => 'Cash'],
            'admin.cash-bank.transactions' => ['reference' => 'Sale #'.$sale->id, 'type_badge' => 'Credit'],
        ];

        foreach ($expectations as $routeName => $cells) {
            $response = $this->actingAs($this->admin)
                ->getJson(route($routeName, ['draw' => 1, 'start' => 0, 'length' => 10]),
                    ['X-Requested-With' => 'XMLHttpRequest']);

            $response->assertOk()->assertJsonPath('recordsTotal', 1);

            $row = $response->json('data.0');

            foreach ($cells as $key => $expected) {
                $this->assertArrayHasKey($key, $row, "{$routeName} is missing the {$key} column");
                $this->assertStringContainsString($expected, $row[$key], "{$routeName} rendered {$key} unexpectedly");
            }
        }
    }

    public function test_product_listing_filters_by_search_term(): void
    {
        $category = Category::create(['name' => 'Snacks', 'status' => true]);
        $unit = Unit::create(['name' => 'Box']);

        foreach (['Salted Chips', 'Chocolate Bar'] as $name) {
            Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'unit_id' => $unit->id,
                'mrp_price' => 20,
                'purchase_price' => 10,
                'sale_price' => 15,
                'status' => true,
            ]);
        }

        $this->actingAs($this->admin)
            ->getJson(route('admin.products.index', [
                'draw' => 1, 'start' => 0, 'length' => 10, 'q' => 'Chips',
            ]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 1);
    }
}
