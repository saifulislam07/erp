<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Super Admin',
                'password'          => 'password',
                'is_admin'          => true,
                'status'            => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Admin');
    }
}
