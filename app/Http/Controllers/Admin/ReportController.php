<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Exports\ProfitExport;
use App\Exports\StockExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function profit(Request $request): View
    {
        $summary = $this->calculateProfit($request);

        return view('admin.reports.profit', [
            'summary' => $summary,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);
    }

    public function profitExcel(Request $request)
    {
        $summary = $this->calculateProfit($request);

        return Excel::download(new ProfitExport($summary), 'profit-report.xlsx');
    }

    public function profitPdf(Request $request)
    {
        $summary = $this->calculateProfit($request);

        $pdf = Pdf::loadView('admin.reports.profit-pdf', [
            'summary' => $summary,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);

        return $pdf->download('profit-report.pdf');
    }

    public function stock(Request $request): View
    {
        $stocks = $this->filteredStockQuery($request)->get();
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        $stores = Store::orderBy('name')->get();

        return view('admin.reports.stock', [
            'stocks' => $stocks,
            'categories' => $categories,
            'stores' => $stores,
            'totalValue' => $stocks->sum(fn ($s) => $s->quantity * $s->purchase_price),
        ]);
    }

    public function stockExcel(Request $request)
    {
        $stocks = $this->filteredStockQuery($request)->get();

        return Excel::download(new StockExport($stocks), 'stock-report.xlsx');
    }

    public function stockPdf(Request $request)
    {
        $stocks = $this->filteredStockQuery($request)->get();

        $pdf = Pdf::loadView('admin.reports.stock-pdf', [
            'stocks' => $stocks,
            'totalValue' => $stocks->sum(fn ($s) => $s->quantity * $s->purchase_price),
        ]);

        return $pdf->download('stock-report.pdf');
    }

    public function orders(Request $request): View
    {
        $orders = $this->filteredOrdersQuery($request)->get();
        $clients = Client::orderBy('name')->get();

        return view('admin.reports.orders', compact('orders', 'clients'));
    }

    public function ordersExcel(Request $request)
    {
        $orders = $this->filteredOrdersQuery($request)->get();

        return Excel::download(new OrdersExport($orders), 'orders-report.xlsx');
    }

    public function ordersPdf(Request $request)
    {
        $orders = $this->filteredOrdersQuery($request)->get();

        $pdf = Pdf::loadView('admin.reports.orders-pdf', [
            'orders' => $orders,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);

        return $pdf->download('orders-report.pdf');
    }

    private function calculateProfit(Request $request): array
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $salesQuery = Sale::query();

        if ($fromDate) {
            $salesQuery->whereDate('sale_date', '>=', $fromDate);
        }

        if ($toDate) {
            $salesQuery->whereDate('sale_date', '<=', $toDate);
        }

        $grossRevenue = (clone $salesQuery)->sum('total_amount');
        $vatCollected = (clone $salesQuery)->sum('vat_amount');

        $costOfGoods = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->when($fromDate, fn ($q) => $q->whereDate('sales.sale_date', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('sales.sale_date', '<=', $toDate))
            ->sum(DB::raw('sale_items.quantity * products.purchase_price'));

        $grossProfit = $grossRevenue - $costOfGoods;

        $expensesQuery = DB::table('expenses');

        if ($fromDate) {
            $expensesQuery->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate) {
            $expensesQuery->whereDate('expense_date', '<=', $toDate);
        }

        $totalExpenses = $expensesQuery->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;

        return [
            'gross_revenue' => (float) $grossRevenue,
            'cost_of_goods' => (float) $costOfGoods,
            'gross_profit' => (float) $grossProfit,
            'total_expenses' => (float) $totalExpenses,
            'vat_collected' => (float) $vatCollected,
            'net_profit' => (float) $netProfit,
        ];
    }

    private function filteredStockQuery(Request $request)
    {
        $query = Stock::with(['product.category', 'product.unit', 'store'])->where('quantity', '>', 0);

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->category_id) {
            $query->whereHas('product', fn ($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        return $query->latest();
    }

    private function filteredOrdersQuery(Request $request)
    {
        $query = Order::with('client');

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        return $query->latest();
    }
}
