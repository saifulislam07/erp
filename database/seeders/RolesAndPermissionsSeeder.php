<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'department.view', 'department.create', 'department.edit', 'department.delete',
            'role.view', 'user.view', 'user.create', 'user.edit', 'user.delete',
            'client.view', 'client.create', 'client.edit', 'client.delete',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'stock.view', 'stock.create', 'stock.edit', 'stock.delete',
            'purchase.view', 'purchase.create', 'purchase.edit', 'purchase.delete',
            'sale.view', 'sale.create', 'sale.edit', 'sale.delete', 'sale.client_agent', 'sale.discount',
            'order.view', 'order.create', 'order.edit', 'order.delete',
            'expense.view', 'expense.create', 'expense.edit', 'expense.delete',
            'cash.view', 'cash.create', 'cash.edit', 'cash.delete',
            'asset.view', 'asset.create', 'asset.edit', 'asset.delete',
            'report.view', 'invoice.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = ['Admin', 'Accountant', 'Employee', 'Local Seller', 'Store Manager'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Role::findByName('Admin')->syncPermissions($permissions);

        Role::findByName('Employee')->syncPermissions(['sale.view', 'sale.create']);
        Role::findByName('Local Seller')->syncPermissions(['sale.view', 'sale.create']);
    }
}
