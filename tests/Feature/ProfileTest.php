<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * `/admin/profile` is the one admin screen every authenticated user can open
 * regardless of role, and nothing on it can reach another user's record.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.profile.edit'));

        $response->assertOk();
    }

    public function test_profile_page_requires_authentication(): void
    {
        $this->get(route('admin.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put(route('admin.profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '01700000000',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('01700000000', $user->phone);
    }

    public function test_email_must_not_be_taken_by_another_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), [
                'name' => 'Test User',
                'email' => $other->email,
            ]);

        $response->assertSessionHasErrors('email');

        $this->assertNotSame($other->email, $user->refresh()->email);
    }

    public function test_keeping_the_same_email_is_allowed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put(route('admin.profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'));
    }

    public function test_password_can_be_changed_from_the_profile_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put(route('admin.profile.password'), [
                'current_password' => 'password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_current_password_must_be_provided_to_change_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.password'), [
                'current_password' => 'wrong-password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]);

        $response->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
