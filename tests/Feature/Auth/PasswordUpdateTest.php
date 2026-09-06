<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Changing your own password from the profile screen. The current password
 * must be proved first, so a hijacked session cannot lock the owner out.
 */
class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.password'), [
                'current_password' => 'password',
                'new_password' => 'new-password-1A',
                'new_password_confirmation' => 'new-password-1A',
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('new-password-1A', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.password'), [
                'current_password' => 'wrong-password',
                'new_password' => 'new-password-1A',
                'new_password_confirmation' => 'new-password-1A',
            ])
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.password'), [
                'current_password' => 'password',
                'new_password' => 'new-password-1A',
                'new_password_confirmation' => 'something-else',
            ])
            ->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
