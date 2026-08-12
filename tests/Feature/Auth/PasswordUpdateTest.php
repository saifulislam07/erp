<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * `/admin/password/change` predates the profile page and is kept so existing
 * links and bookmarks still work. The GET redirects onto the profile; the PUT
 * still changes the password.
 */
class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_password_page_redirects_to_the_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.password.edit'))
            ->assertRedirect(route('admin.profile.edit'));
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'wrong-password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]);

        $response->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'different-password',
            ]);

        $response->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
