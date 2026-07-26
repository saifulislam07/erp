<?php

namespace App\Support\AdminLte;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class PendingOrdersBadgeFilter implements FilterInterface
{
    public function transform($item)
    {
        if (($item['route'] ?? null) !== 'admin.orders.index') {
            return $item;
        }

        if (! Auth::guard('web')->check() || ! Schema::hasTable('orders')) {
            return $item;
        }

        $pending = Order::where('status', 'pending')->count();

        if ($pending > 0) {
            $item['label'] = $pending;
            $item['label_color'] = 'warning';
        }

        return $item;
    }
}
