<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * There is no public landing page — `/` always hands off to the login screen or
 * to whichever admin page the signed-in user is allowed to open.
 */
class RootRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_screen(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_signed_in_users_are_sent_to_their_landing_page(): void
    {
        $user = User::factory()->create(['status' => true]);

        $this->actingAs($user)->get('/')->assertRedirect(route('admin.home'));
    }

    public function test_users_with_dashboard_access_are_sent_to_the_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create(['is_admin' => true, 'status' => true]);
        $user->assignRole('Admin');

        $this->actingAs($user)->get('/')->assertRedirect(route('admin.dashboard'));
    }
}
