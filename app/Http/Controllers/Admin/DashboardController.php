<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Models\User;
use App\Services\CashBankService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService)
    {
    }

    public function index(): View
    {
        $user = auth()->user();

        // Every widget is scoped to the permission that guards the module it
        // summarises, so the dashboard never reveals figures (cash balances,
        // client counts, ...) from a module the user cannot otherwise open.
        $can = [
            'client' => $user->can('client.view'),
            'product' => $user->can('product.view'),
            'stock' => $user->can('stock.view'),
            'sale' => $user->can('sale.view'),
            'purchase' => $user->can('purchase.view'),
            'order' => $user->can('order.view'),
            'cash' => $user->can('cash.view'),
            'expense' => $user->can('expense.view'),
            'report' => $user->can('report.view'),
            'user' => $user->can('user.view'),
            'department' => $user->can('department.view'),
        ];

        $lowStockAlerts = $can['stock'] && Schema::hasTable('products') && Schema::hasTable('stocks')
            ? Product::where('min_stock_threshold', '>', 0)->get()->filter(function (Product $product) {
                $available = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');

                return $available < $product->min_stock_threshold;
            })->count()
            : 0;

        $cards = [
            'total_clients' => $can['client'] && Schema::hasTable('clients') ? DB::table('clients')->count() : 0,
            'total_products' => $can['product'] && Schema::hasTable('products') ? DB::table('products')->count() : 0,
            'total_stock_value' => $can['stock'] && Schema::hasTable('stocks') ? DB::table('stocks')->sum('quantity') : 0,
            'todays_sales' => $can['sale'] && Schema::hasTable('sales') ? DB::table('sales')->whereDate('sale_date', today())->sum('total_amount') : 0,
            'pending_orders' => $can['order'] && Schema::hasTable('orders') ? DB::table('orders')->where('status', 'pending')->count() : 0,
            'low_stock_alerts' => $lowStockAlerts,
        ];

        $cashWidgets = [
            'cash_balance' => $can['cash'] && Schema::hasTable('cash_bank_transactions') ? $this->cashBankService->getCashBalance() : 0,
            'bank_balance' => $can['cash'] && Schema::hasTable('cash_bank_transactions') ? $this->cashBankService->getBankBalance() : 0,
            'todays_expenses' => $can['expense'] && Schema::hasTable('expenses') ? DB::table('expenses')->whereDate('expense_date', today())->sum('amount') : 0,
        ];

        $totalDepartments = $can['department'] ? Department::count() : 0;
        $totalEmployees = $can['user'] ? User::count() : 0;

        $salesChart = $can['sale'] ? $this->salesLast30Days() : ['labels' => [], 'totals' => []];
        $topProducts = $can['sale'] ? $this->topSellingProducts() : [];
        $orderStatusDistribution = $can['order'] ? $this->orderStatusDistribution() : ['labels' => [], 'counts' => []];
        $recentActivities = $this->recentActivities($can);

        return view('admin.dashboard', compact(
            'can',
            'cards',
            'cashWidgets',
            'totalDepartments',
            'totalEmployees',
            'salesChart',
            'topProducts',
            'orderStatusDistribution',
            'recentActivities',
        ));
    }

    private function salesLast30Days(): array
    {
        if (! Schema::hasTable('sales')) {
            return ['labels' => [], 'totals' => []];
        }

        $rows = DB::table('sales')
            ->selectRaw('sale_date, SUM(total_amount) as total')
            ->where('sale_date', '>=', now()->subDays(29)->toDateString())
            ->groupBy('sale_date')
            ->pluck('total', 'sale_date');

        $labels = [];
        $totals = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('M d');
            $totals[] = (float) ($rows[$date] ?? 0);
        }

        return ['labels' => $labels, 'totals' => $totals];
    }

    private function topSellingProducts(): array
    {
        if (! Schema::hasTable('sale_items')) {
            return [];
        }

        return DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->selectRaw('products.name as name, SUM(sale_items.quantity) as qty')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'qty' => (float) $row->qty])
            ->toArray();
    }

    private function orderStatusDistribution(): array
    {
        if (! Schema::hasTable('orders')) {
            return ['labels' => [], 'counts' => []];
        }

        $rows = DB::table('orders')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'labels' => $rows->keys()->map(fn ($status) => ucfirst(str_replace('_', ' ', $status)))->values()->toArray(),
            'counts' => $rows->values()->toArray(),
        ];
    }

    /**
     * @param  array<string, bool>  $can
     */
    private function recentActivities(array $can): array
    {
        $activities = collect();

        if ($can['order'] && Schema::hasTable('orders')) {
            DB::table('orders')->latest('created_at')->limit(10)->get()->each(function ($order) use ($activities) {
                $activities->push([
                    'type' => 'Order',
                    'description' => "Order {$order->order_id} - ".ucfirst(str_replace('_', ' ', $order->status)),
                    'amount' => $order->total_amount,
                    'date' => $order->created_at,
                ]);
            });
        }

        if ($can['sale'] && Schema::hasTable('sales')) {
            DB::table('sales')->latest('created_at')->limit(10)->get()->each(function ($sale) use ($activities) {
                $activities->push([
                    'type' => 'Sale',
                    'description' => "Sale {$sale->sale_id} to ".($sale->customer_name ?: 'Walk-in customer'),
                    'amount' => $sale->total_amount,
                    'date' => $sale->created_at,
                ]);
            });
        }

        if ($can['purchase'] && Schema::hasTable('purchases')) {
            DB::table('purchases')->latest('created_at')->limit(10)->get()->each(function ($purchase) use ($activities) {
                $activities->push([
                    'type' => 'Purchase',
                    'description' => "Purchase {$purchase->purchase_id}",
                    'amount' => $purchase->total_amount,
                    'date' => $purchase->created_at,
                ]);
            });
        }

        return $activities->sortByDesc('date')->take(10)->values()->toArray();
    }
}
