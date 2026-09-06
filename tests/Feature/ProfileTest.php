<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The signed-in user's own account screen. It is deliberately ungated — every
 * authenticated user reaches it — so these tests also pin down that nothing
 * here can touch another account.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertOk();
    }

    public function test_profile_page_needs_no_permission(): void
    {
        // A user with no roles and no permissions at all still gets in.
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '01700000000',
            'address' => 'Dhaka',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('01700000000', $user->phone);
    }

    public function test_email_must_not_belong_to_another_account(): void
    {
        $other = User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), [
                'name' => 'Test User',
                'email' => $other->email,
            ])
            ->assertSessionHasErrors('email');

        $this->assertNotSame('taken@example.com', $user->fresh()->email);
    }

    public function test_a_user_can_keep_their_own_email_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.profile.update'), [
                'name' => 'Same Email',
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Same Email', $user->fresh()->name);
    }
}
