<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * The sign-in pages are standalone — they do not extend the panel layout, so
     * they have to pull in the Bootstrap/AdminLTE base themselves. Without it the
     * form markup (.input-group, .form-control, .btn) renders unstyled.
     *
     * @param  string  $route
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('guestPages')]
    public function test_guest_pages_load_the_stylesheets_their_markup_needs(string $route): void
    {
        $response = $this->get($route);

        $response->assertOk()
            ->assertSee('assets/core/css/base.min.css', escape: false)
            ->assertSee('assets/css/theme.css', escape: false);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function guestPages(): array
    {
        return [
            'login' => ['/login'],
            'forgot password' => ['/forgot-password'],
        ];
    }

    /**
     * Users holding `dashboard.view` land on the dashboard; see App\Support\AdminLanding.
     */
    public function test_users_with_dashboard_access_land_on_the_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create(['is_admin' => true, 'status' => true]);
        $user->assignRole('Admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Everyone else lands on the neutral home page, which never 403s.
     */
    public function test_users_without_dashboard_access_land_on_the_home_page(): void
    {
        $user = User::factory()->create(['status' => true]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.home'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
