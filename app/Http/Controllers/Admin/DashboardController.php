<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use App\Services\CashBankService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService) {}

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

        return view('admin.dashboard', [
            'can' => $can,
            'today' => $this->todayFigures($can),
            'position' => $this->financialPosition($can),
            'catalogue' => $this->catalogueFigures($can),
            'team' => [
                'departments' => $can['department'] ? Department::count() : 0,
                'employees' => $can['user'] ? User::where('is_admin', false)->count() : 0,
            ],
            'salesChart' => $can['sale'] ? $this->salesLast30Days() : ['labels' => [], 'totals' => []],
            'topProducts' => $can['sale'] ? $this->topSellingProducts() : [],
            'orderStatusDistribution' => $can['order'] ? $this->orderStatusDistribution() : ['labels' => [], 'counts' => []],
            'recentActivities' => $this->recentActivities($can),
        ]);
    }

    /**
     * What happened today: revenue in, spend out, orders waiting.
     *
     * @param  array<string, bool>  $can
     * @return array<string, float|int>
     */
    private function todayFigures(array $can): array
    {
        return [
            'sales_total' => $can['sale'] && Schema::hasTable('sales')
                ? (float) DB::table('sales')->whereNull('deleted_at')->whereDate('sale_date', today())->sum('total_amount')
                : 0.0,
            'sales_count' => $can['sale'] && Schema::hasTable('sales')
                ? DB::table('sales')->whereNull('deleted_at')->whereDate('sale_date', today())->count()
                : 0,
            'purchases_total' => $can['purchase'] && Schema::hasTable('purchases')
                ? (float) DB::table('purchases')->whereNull('deleted_at')->whereDate('purchase_date', today())->sum('total_amount')
                : 0.0,
            'expenses_total' => $can['expense'] && Schema::hasTable('expenses')
                ? (float) DB::table('expenses')->whereDate('expense_date', today())->sum('amount')
                : 0.0,
            'pending_orders' => $can['order'] && Schema::hasTable('orders')
                ? DB::table('orders')->where('status', 'pending')->count()
                : 0,
        ];
    }

    /**
     * Money the business holds, is owed, and owes.
     *
     * @param  array<string, bool>  $can
     * @return array<string, float>
     */
    private function financialPosition(array $can): array
    {
        $hasCash = $can['cash'] && Schema::hasTable('cash_bank_transactions');

        return [
            'cash_balance' => $hasCash ? $this->cashBankService->getCashBalance() : 0.0,
            'bank_balance' => $hasCash ? $this->cashBankService->getBankBalance() : 0.0,

            // Receivable is what customers still owe on their invoices;
            // payable is what is still owed to suppliers on purchases.
            'receivable' => $can['sale'] && Schema::hasTable('sales')
                ? (float) DB::table('sales')->whereNull('deleted_at')->sum('due_amount')
                : 0.0,
            'payable' => $can['purchase'] && Schema::hasTable('purchases')
                ? (float) DB::table('purchases')->whereNull('deleted_at')->sum('due_amount')
                : 0.0,
        ];
    }

    /**
     * Catalogue and inventory health.
     *
     * @param  array<string, bool>  $can
     * @return array<string, float|int>
     */
    private function catalogueFigures(array $can): array
    {
        $hasStock = $can['stock'] && Schema::hasTable('stocks');

        // Stock *value* is quantity × what the goods cost, not a bare count —
        // the previous version summed quantities and labelled it a value.
        $stockValue = $hasStock
            ? (float) DB::table('stocks')->selectRaw('SUM(quantity * purchase_price) as v')->value('v')
            : 0.0;

        $defaultThreshold = (float) Setting::get('low_stock_threshold_default', 0);

        // One grouped query instead of a query per product.
        $lowStock = 0;
        if ($hasStock && Schema::hasTable('products')) {
            $lowStock = DB::table('products')
                ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
                ->whereNull('products.deleted_at')
                ->where('products.status', true)
                ->groupBy('products.id', 'products.min_stock_threshold')
                ->havingRaw(
                    'COALESCE(SUM(stocks.quantity), 0) <= COALESCE(NULLIF(products.min_stock_threshold, 0), ?)
                     AND COALESCE(NULLIF(products.min_stock_threshold, 0), ?) > 0',
                    [$defaultThreshold, $defaultThreshold]
                )
                ->get(['products.id'])
                ->count();
        }

        return [
            'clients' => $can['client'] && Schema::hasTable('clients')
                ? DB::table('clients')->whereNull('deleted_at')->count()
                : 0,
            'products' => $can['product'] && Schema::hasTable('products')
                ? DB::table('products')->whereNull('deleted_at')->count()
                : 0,
            'stock_value' => $stockValue,
            'stock_units' => $hasStock ? (float) DB::table('stocks')->sum('quantity') : 0.0,
            'low_stock' => $lowStock,
        ];
    }

    /**
     * @return array{labels: list<string>, totals: list<float>}
     */
    private function salesLast30Days(): array
    {
        if (! Schema::hasTable('sales')) {
            return ['labels' => [], 'totals' => []];
        }

        $rows = DB::table('sales')
            ->selectRaw('sale_date, SUM(total_amount) as total')
            ->whereNull('deleted_at')
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
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')
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
                    'tone' => 'info',
                    'description' => "Order {$order->order_id} — ".ucfirst(str_replace('_', ' ', $order->status)),
                    'amount' => (float) $order->total_amount,
                    'date' => $order->created_at,
                    'url' => route('admin.orders.show', $order->id),
                ]);
            });
        }

        if ($can['sale'] && Schema::hasTable('sales')) {
            DB::table('sales')->whereNull('deleted_at')->latest('created_at')->limit(10)->get()->each(function ($sale) use ($activities) {
                $activities->push([
                    'type' => 'Sale',
                    'tone' => 'success',
                    'description' => "Sale {$sale->sale_id} to ".($sale->customer_name ?: 'walk-in customer'),
                    'amount' => (float) $sale->total_amount,
                    'date' => $sale->created_at,
                    'url' => route('admin.sales.show', $sale->id),
                ]);
            });
        }

        if ($can['purchase'] && Schema::hasTable('purchases')) {
            DB::table('purchases')->whereNull('deleted_at')->latest('created_at')->limit(10)->get()->each(function ($purchase) use ($activities) {
                $activities->push([
                    'type' => 'Purchase',
                    'tone' => 'warning',
                    'description' => "Purchase {$purchase->purchase_id}",
                    'amount' => (float) $purchase->total_amount,
                    'date' => $purchase->created_at,
                    'url' => route('admin.purchases.show', $purchase->id),
                ]);
            });
        }

        return $activities->sortByDesc('date')->take(12)->values()->toArray();
    }
}
