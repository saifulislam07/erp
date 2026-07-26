<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
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
        $cards = [
            'total_clients' => Schema::hasTable('clients') ? DB::table('clients')->count() : 0,
            'total_products' => Schema::hasTable('products') ? DB::table('products')->count() : 0,
            'total_stock_value' => Schema::hasTable('stocks') ? DB::table('stocks')->sum('quantity') : 0,
            'todays_sales' => Schema::hasTable('sales') ? DB::table('sales')->whereDate('sale_date', today())->sum('total_amount') : 0,
            'pending_orders' => Schema::hasTable('orders') ? DB::table('orders')->where('status', 'pending')->count() : 0,
            'low_stock_alerts' => 0,
        ];

        $cashWidgets = [
            'cash_balance' => Schema::hasTable('cash_bank_transactions') ? $this->cashBankService->getCashBalance() : 0,
            'bank_balance' => Schema::hasTable('cash_bank_transactions') ? $this->cashBankService->getBankBalance() : 0,
            'todays_expenses' => Schema::hasTable('expenses') ? DB::table('expenses')->whereDate('expense_date', today())->sum('amount') : 0,
        ];

        $totalDepartments = Department::count();
        $totalEmployees = User::count();

        return view('admin.dashboard', compact('cards', 'cashWidgets', 'totalDepartments', 'totalEmployees'));
    }
}
