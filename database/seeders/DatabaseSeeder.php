<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
        ]);

        $this->createAdmin();
    }

    /**
     * Create the first administrator.
     *
     * The password is never a fixed literal: set ADMIN_PASSWORD to choose one,
     * otherwise a random password is generated and printed once. That way a
     * production deploy cannot silently end up with a publicly known password,
     * and nobody has to remember to change it afterwards.
     *
     * Re-running the seeder never rewrites an existing account's password —
     * an admin who has already changed theirs would otherwise be reset back.
     */
    private function createAdmin(): void {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $chosen = env('ADMIN_PASSWORD');
        $password = $chosen ?: Str::password(16);

        $existing = User::withTrashed()->where('email', $email)->first();

        if ($existing) {
            $existing->assignRole('Admin');

            $this->command?->warn("Admin {$email} already exists — password left unchanged.");

            return;
        }

        $admin = User::create([
            'name'              => 'Super Admin',
            'email'             => $email,
            'password'          => $password,
            'is_admin'          => true,
            'status'            => true,
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('Admin');

        $this->command?->info("Admin account created: {$email}");

        if ($chosen) {
            $this->command?->info('Password: taken from ADMIN_PASSWORD.');

            return;
        }

        // Shown once, and only here — it is not stored anywhere in readable form.
        $this->command?->warn("Generated password: {$password}");
        $this->command?->warn('Copy it now and change it after the first sign-in; it cannot be recovered.');
    }
}
