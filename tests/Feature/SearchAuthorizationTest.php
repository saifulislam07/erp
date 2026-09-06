<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression: every `search/*` endpoint sat outside the permission groups and
 * the controller authorised nothing, so any signed-in user could read supplier
 * contact details, order totals, sale records and stock levels straight out of
 * the JSON endpoints — data the module screens would never have shown them.
 */
class SearchAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function gatedEndpoints(): array
    {
        return [
            'suppliers' => ['admin.search.suppliers', 'purchase.view'],
            'orders' => ['admin.search.orders', 'order.view'],
            'sales' => ['admin.search.sales', 'sale.view'],
            'returns' => ['admin.search.returns', 'return.view'],
            'stocks' => ['admin.search.stocks', 'stock.view'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('gatedEndpoints')]
    public function test_a_user_without_the_module_permission_is_refused(string $route, string $permission): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route($route, ['q' => 'a']))
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('gatedEndpoints')]
    public function test_a_user_holding_the_module_permission_gets_through(string $route, string $permission): void
    {
        $this->actingAs($this->userWith($permission))
            ->getJson(route($route, ['q' => 'a']))
            ->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('gatedEndpoints')]
    public function test_an_admin_gets_through(string $route, string $permission): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->getJson(route($route, ['q' => 'a']))
            ->assertOk();
    }

    public function test_the_navbar_search_stays_open_to_everyone(): void
    {
        // A user with no permissions must still be able to type in the box —
        // they simply get nothing back.
        $this->actingAs(User::factory()->create())
            ->getJson(route('admin.search.global', ['q' => 'Widget']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_the_navbar_search_only_returns_modules_the_user_can_open(): void
    {
        Product::factory()->create(['name' => 'Widget Deluxe']);
        Supplier::factory()->create(['name' => 'Widget Supplies Ltd']);
        Client::factory()->create(['name' => 'Widget Buyers Ltd']);

        $response = $this->actingAs($this->userWith('product.view'))
            ->getJson(route('admin.search.global', ['q' => 'Widget']))
            ->assertOk();

        $types = collect($response->json())->pluck('type')->unique()->all();

        $this->assertSame(['Product'], $types);
    }

    public function test_an_admin_sees_every_section_of_the_navbar_search(): void
    {
        Product::factory()->create(['name' => 'Widget Deluxe']);
        Supplier::factory()->create(['name' => 'Widget Supplies Ltd']);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->getJson(route('admin.search.global', ['q' => 'Widget']))
            ->assertOk();

        $types = collect($response->json())->pluck('type')->all();

        $this->assertContains('Product', $types);
        $this->assertContains('Supplier', $types);
    }

    private function userWith(string $permission): User
    {
        $user = User::factory()->create();
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);

        return $user->fresh();
    }
}
