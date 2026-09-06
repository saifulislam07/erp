<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The first administrator. A seeded account with a password everybody knows is
 * the kind of thing that survives all the way into production, so the seeder
 * must never plant one — and it must never overwrite a password that has
 * already been changed.
 */
class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seeded_admin_has_no_well_known_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->sole();

        $this->assertFalse(Hash::check('password', $admin->password));
        $this->assertFalse(Hash::check('admin', $admin->password));
        $this->assertFalse(Hash::check('12345678', $admin->password));
    }

    public function test_the_seeded_admin_is_an_active_super_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->sole();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue($admin->status);
        $this->assertTrue($admin->hasRole('Admin'));
    }

    public function test_an_explicit_password_is_honoured(): void
    {
        putenv('ADMIN_PASSWORD=chosen-secret-1A');
        putenv('ADMIN_EMAIL=owner@example.com');

        try {
            $this->seed(DatabaseSeeder::class);

            $admin = User::where('email', 'owner@example.com')->sole();

            $this->assertTrue(Hash::check('chosen-secret-1A', $admin->password));
        } finally {
            putenv('ADMIN_PASSWORD');
            putenv('ADMIN_EMAIL');
        }
    }

    public function test_reseeding_does_not_reset_a_changed_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->sole();
        $admin->update(['password' => 'the-owners-own-password-1A']);

        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Hash::check('the-owners-own-password-1A', $admin->fresh()->password));
        $this->assertSame(1, User::where('email', 'admin@example.com')->count());
    }
}
