<?php

namespace App\Support;

use App\Models\User;

class AdminLanding
{
    /**
     * Every permission-gated area a user can be sent to, in preference order.
     * Each entry drives both the post-login landing choice and the shortcut
     * tiles rendered on the fallback home page.
     *
     * @var array<string, array{label: string, route: string, icon: string, color: string}>
     */
    private const array MODULES = [
        'sale.view' => ['label' => 'Sales', 'route' => 'admin.sales.index', 'icon' => 'fas fa-cash-register', 'color' => 'lime'],
        'order.view' => ['label' => 'Orders', 'route' => 'admin.orders.index', 'icon' => 'fas fa-shopping-cart', 'color' => 'danger'],
        'purchase.view' => ['label' => 'Purchases', 'route' => 'admin.purchases.index', 'icon' => 'fas fa-truck-loading', 'color' => 'maroon'],
        'stock.view' => ['label' => 'Stock', 'route' => 'admin.stocks.index', 'icon' => 'fas fa-warehouse', 'color' => 'warning'],
        'product.view' => ['label' => 'Products', 'route' => 'admin.products.index', 'icon' => 'fas fa-box-open', 'color' => 'orange'],
        'delivery.view' => ['label' => 'Store & Delivery', 'route' => 'admin.deliveries.index', 'icon' => 'fas fa-shipping-fast', 'color' => 'teal'],
        'return.view' => ['label' => 'Returns', 'route' => 'admin.returns.index', 'icon' => 'fas fa-undo', 'color' => 'fuchsia'],
        'expense.view' => ['label' => 'Expenses', 'route' => 'admin.expenses.index', 'icon' => 'fas fa-money-bill-wave', 'color' => 'warning'],
        'cash.view' => ['label' => 'Cash & Bank', 'route' => 'admin.cash-bank.index', 'icon' => 'fas fa-university', 'color' => 'olive'],
        'asset.view' => ['label' => 'Assets', 'route' => 'admin.assets.index', 'icon' => 'fas fa-boxes', 'color' => 'indigo'],
        'report.view' => ['label' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'fas fa-chart-bar', 'color' => 'cyan'],
        'client.view' => ['label' => 'Clients / Agents', 'route' => 'admin.clients.index', 'icon' => 'fas fa-handshake', 'color' => 'success'],
        'message.view' => ['label' => 'Messages', 'route' => 'admin.messages.index', 'icon' => 'fas fa-comments', 'color' => 'warning'],
        'user.view' => ['label' => 'Employees', 'route' => 'admin.employees.index', 'icon' => 'fas fa-user-tie', 'color' => 'navy'],
        'department.view' => ['label' => 'Departments', 'route' => 'admin.departments.index', 'icon' => 'fas fa-building', 'color' => 'primary'],
        'role.view' => ['label' => 'Roles', 'route' => 'admin.roles.index', 'icon' => 'fas fa-user-shield', 'color' => 'purple'],
    ];

    /**
     * The route name a freshly authenticated user should land on. Users holding
     * `dashboard.view` get the dashboard; everyone else gets the neutral home
     * page, which never 403s and links onward to whatever they can open.
     */
    public static function routeFor(?User $user): string
    {
        if ($user && $user->can('dashboard.view')) {
            return 'admin.dashboard';
        }

        return 'admin.home';
    }

    public static function urlFor(?User $user): string
    {
        return route(self::routeFor($user));
    }

    /**
     * Shortcut tiles for the modules this user may actually open.
     *
     * @return list<array{label: string, route: string, icon: string, color: string}>
     */
    public static function accessibleModules(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $modules = [];

        foreach (self::MODULES as $permission => $module) {
            if ($user->can($permission)) {
                $modules[] = $module;
            }
        }

        return $modules;
    }
}
