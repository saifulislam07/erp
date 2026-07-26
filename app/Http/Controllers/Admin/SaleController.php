<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SalesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaleRequest;
use App\Models\Account;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Store;
use App\Services\CashBankService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly CashBankService $cashBankService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sale::class);

        $query = Sale::with(['customer', 'creator']);

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('sale_date', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('sale_date', '<=', $toDate);
        }

        if ($customerType = $request->get('customer_type')) {
            $query->where('customer_type', $customerType);
        }

        if ($productName = $request->get('product_name')) {
            $query->whereHas('items.product', fn ($q) => $q->where('name', 'like', "%{$productName}%"));
        }

        $sales = $query->latest()->get();

        return view('admin.sales.index', compact('sales'));
    }

    public function create(): View
    {
        $this->authorize('create', Sale::class);

        $stores = Store::where('status', true)->orderBy('name')->get();
        $canSellToClientAgent = auth()->user()->can('sellToClientAgent', Sale::class);
        $canApplyDiscount = auth()->user()->can('applyDiscount', Sale::class);

        return view('admin.sales.create', compact('stores', 'canSellToClientAgent', 'canApplyDiscount'));
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $items = $this->sanitizeItems($request);
            [$subtotal, $discountAmount, $vatAmount, $total] = $this->calculateTotals($items);

            $sale = Sale::create([
                'customer_type' => $request->customer_type,
                'customer_id' => $request->customer_type === 'client_agent' ? $request->customer_id : null,
                'customer_name' => $request->customer_type === 'local' ? $request->customer_name : null,
                'sale_date' => $request->sale_date,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_amount' => $vatAmount,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount ?? 0,
                'transaction_reference' => $request->transaction_reference,
                'note' => $request->note,
                'created_by' => $request->user()->id,
            ]);

            $this->createItemsAndDeductStock($sale, $items, $request->user()->id);

            if ($sale->paid_amount > 0) {
                $this->cashBankService->credit(
                    amount: $sale->paid_amount,
                    method: $sale->payment_method,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    description: "Payment for sale {$sale->sale_id}",
                    userId: $request->user()->id,
                );
            }

            $this->syncReceivable($sale, $request->user()->id);
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale created successfully.');
    }

    public function show(Sale $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['customer', 'items.product', 'items.store', 'creator']);

        return view('admin.sales.show', compact('sale'));
    }

    public function edit(Sale $sale): View
    {
        $this->authorize('update', $sale);

        $sale->load('items');
        $stores = Store::where('status', true)->orderBy('name')->get();
        $canSellToClientAgent = auth()->user()->can('sellToClientAgent', Sale::class);
        $canApplyDiscount = auth()->user()->can('applyDiscount', Sale::class);

        return view('admin.sales.edit', compact('sale', 'stores', 'canSellToClientAgent', 'canApplyDiscount'));
    }

    public function update(SaleRequest $request, Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($request, $sale) {
            foreach ($sale->items as $oldItem) {
                $this->stockService->addStock(
                    productId: $oldItem->product_id,
                    storeId: $oldItem->store_id,
                    quantity: (float) $oldItem->quantity,
                    purchasePrice: $oldItem->product->purchase_price ?? 0,
                    expiryDate: null,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    createdBy: $request->user()->id,
                    note: "Reversal for editing sale {$sale->sale_id}",
                );
            }

            if ($sale->paid_amount > 0) {
                $this->cashBankService->debit(
                    amount: $sale->paid_amount,
                    method: $sale->payment_method,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    description: "Reversal of payment for sale {$sale->sale_id}",
                    userId: $request->user()->id,
                );
            }

            $sale->items()->delete();

            $items = $this->sanitizeItems($request);
            [$subtotal, $discountAmount, $vatAmount, $total] = $this->calculateTotals($items);

            $sale->update([
                'customer_type' => $request->customer_type,
                'customer_id' => $request->customer_type === 'client_agent' ? $request->customer_id : null,
                'customer_name' => $request->customer_type === 'local' ? $request->customer_name : null,
                'sale_date' => $request->sale_date,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_amount' => $vatAmount,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount ?? 0,
                'transaction_reference' => $request->transaction_reference,
                'note' => $request->note,
            ]);

            $this->createItemsAndDeductStock($sale, $items, $request->user()->id);

            if ($sale->paid_amount > 0) {
                $this->cashBankService->credit(
                    amount: $sale->paid_amount,
                    method: $sale->payment_method,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    description: "Payment for updated sale {$sale->sale_id}",
                    userId: $request->user()->id,
                );
            }

            $this->syncReceivable($sale, $request->user()->id);
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale updated successfully.');
    }

    public function destroy(Request $request, Sale $sale): RedirectResponse
    {
        $this->authorize('delete', $sale);

        DB::transaction(function () use ($request, $sale) {
            foreach ($sale->items as $item) {
                $this->stockService->addStock(
                    productId: $item->product_id,
                    storeId: $item->store_id,
                    quantity: (float) $item->quantity,
                    purchasePrice: $item->product->purchase_price ?? 0,
                    expiryDate: null,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    createdBy: $request->user()->id,
                    note: "Reversal for deleted sale {$sale->sale_id}",
                );
            }

            if ($sale->paid_amount > 0) {
                $this->cashBankService->debit(
                    amount: $sale->paid_amount,
                    method: $sale->payment_method,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    description: "Reversal of payment for deleted sale {$sale->sale_id}",
                    userId: $request->user()->id,
                );
            }

            Account::where('reference_type', Sale::class)->where('reference_id', $sale->id)->delete();

            $sale->delete();
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale deleted successfully.');
    }

    public function report(Request $request): View
    {
        $this->authorize('viewAny', Sale::class);

        $sales = $this->filteredReportQuery($request)->get();

        return view('admin.sales.reports.index', [
            'sales' => $sales,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
            'totals' => $this->sumTotals($sales),
        ]);
    }

    public function reportExcel(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $sales = $this->filteredReportQuery($request)->get();

        return Excel::download(new SalesExport($sales), 'sales-report.xlsx');
    }

    public function reportPdf(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $sales = $this->filteredReportQuery($request)->get();

        $pdf = Pdf::loadView('admin.sales.reports.pdf', [
            'sales' => $sales,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
            'totals' => $this->sumTotals($sales),
        ]);

        return $pdf->download('sales-report.pdf');
    }

    private function filteredReportQuery(Request $request)
    {
        $query = Sale::with('customer');

        if ($request->from_date) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }

        if ($request->customer_type) {
            $query->where('customer_type', $request->customer_type);
        }

        match ($request->get('period')) {
            'weekly' => $query->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'monthly' => $query->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()]),
            'yearly' => $query->whereBetween('sale_date', [now()->startOfYear(), now()->endOfYear()]),
            default => null,
        };

        return $query->latest();
    }

    private function sumTotals($sales): array
    {
        return [
            'subtotal' => $sales->sum('subtotal'),
            'discount_amount' => $sales->sum('discount_amount'),
            'vat_amount' => $sales->sum('vat_amount'),
            'total_amount' => $sales->sum('total_amount'),
            'paid_amount' => $sales->sum('paid_amount'),
            'due_amount' => $sales->sum('due_amount'),
        ];
    }

    private function syncReceivable(Sale $sale, int $userId): void
    {
        $existing = Account::where('reference_type', Sale::class)
            ->where('reference_id', $sale->id)
            ->first();

        if ($sale->customer_type !== 'client_agent' || $sale->due_amount <= 0) {
            $existing?->delete();

            return;
        }

        Account::updateOrCreate(
            ['reference_type' => Sale::class, 'reference_id' => $sale->id],
            [
                'type' => 'receivable',
                'party_type' => 'client',
                'party_id' => $sale->customer_id,
                'amount' => $sale->due_amount,
                'description' => "Due for sale {$sale->sale_id}",
                'is_settled' => false,
                'created_by' => $existing?->created_by ?? $userId,
            ]
        );
    }

    private function sanitizeItems(Request $request): array
    {
        $canApplyDiscount = $request->user()->can('applyDiscount', Sale::class);
        $items = $request->items;

        if (! $canApplyDiscount) {
            foreach ($items as &$item) {
                $item['discount_amount'] = 0;
            }
        }

        return $items;
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0;
        $discountAmount = 0;
        $vatAmount = 0;

        foreach ($items as $item) {
            $price = (float) $item['unit_price'];
            $qty = (float) $item['quantity'];
            $discount = (float) ($item['discount_amount'] ?? 0);
            $vatPercentage = (float) ($item['vat_percentage'] ?? 0);

            $lineSubtotal = $price * $qty;
            $lineVat = $vatPercentage > 0 ? ($price * $qty * $vatPercentage / 100) : 0;

            $subtotal += $lineSubtotal;
            $discountAmount += $discount;
            $vatAmount += $lineVat;
        }

        $total = $subtotal - $discountAmount + $vatAmount;

        return [$subtotal, $discountAmount, $vatAmount, $total];
    }

    private function createItemsAndDeductStock(Sale $sale, array $items, int $userId): void
    {
        foreach ($items as $item) {
            $price = (float) $item['unit_price'];
            $qty = (float) $item['quantity'];
            $discount = (float) ($item['discount_amount'] ?? 0);
            $vatPercentage = (float) ($item['vat_percentage'] ?? 0);
            $vatAmount = $vatPercentage > 0 ? ($price * $qty * $vatPercentage / 100) : 0;

            $sale->items()->create([
                'product_id' => $item['product_id'],
                'store_id' => $item['store_id'],
                'quantity' => $qty,
                'unit_price' => $price,
                'discount_amount' => $discount,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'total_price' => ($price * $qty) - $discount + $vatAmount,
            ]);

            $this->stockService->deductStock(
                productId: $item['product_id'],
                storeId: $item['store_id'],
                quantity: $qty,
                referenceType: Sale::class,
                referenceId: $sale->id,
                createdBy: $userId,
                note: "Sale {$sale->sale_id}",
            );
        }
    }
}
