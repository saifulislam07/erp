<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PurchasesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurchaseRequest;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\CashBankService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly CashBankService $cashBankService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Purchase::class);

        $query = Purchase::with('supplier');

        if ($search = $request->get('purchase_id')) {
            $query->where('purchase_id', 'like', "%{$search}%");
        }

        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('purchase_date', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('purchase_date', '<=', $toDate);
        }

        match ($request->get('period')) {
            'weekly' => $query->whereBetween('purchase_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'monthly' => $query->whereBetween('purchase_date', [now()->startOfMonth(), now()->endOfMonth()]),
            'yearly' => $query->whereBetween('purchase_date', [now()->startOfYear(), now()->endOfYear()]),
            default => null,
        };

        $purchases = $query->latest()->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(): View
    {
        $this->authorize('create', Purchase::class);

        $suppliers = Supplier::where('status', true)->orderBy('name')->get();
        $stores = Store::where('status', true)->orderBy('name')->get();

        return view('admin.purchases.create', compact('suppliers', 'stores'));
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        $this->authorize('create', Purchase::class);

        DB::transaction(function () use ($request) {
            [$subtotal, $vatAmount, $total] = $this->calculateTotals($request->items);

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'invoice_number' => $request->invoice_number,
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $total,
                'paid_amount' => $request->paid_amount ?? 0,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
                'created_by' => $request->user()->id,
            ]);

            $this->createItemsAndStock($purchase, $request->items, $request->user()->id);

            if ($purchase->paid_amount > 0) {
                $this->cashBankService->debit(
                    amount: $purchase->paid_amount,
                    method: $purchase->payment_method,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    description: "Payment for purchase {$purchase->purchase_id}",
                    userId: $request->user()->id,
                );
            }

            $this->syncPayable($purchase, $request->user()->id);
        });

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase): View
    {
        $this->authorize('view', $purchase);

        $purchase->load(['supplier', 'items.product', 'items.store', 'returns.items']);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View|RedirectResponse
    {
        $this->authorize('update', $purchase);

        if ($purchase->returns()->exists()) {
            return redirect()->route('admin.purchases.index')->with('error', 'Cannot edit a purchase that has returns.');
        }

        $purchase->load('items');
        $suppliers = Supplier::orderBy('name')->get();
        $stores = Store::where('status', true)->orderBy('name')->get();

        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'stores'));
    }

    public function update(PurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        if ($purchase->returns()->exists()) {
            return redirect()->route('admin.purchases.index')->with('error', 'Cannot edit a purchase that has returns.');
        }

        DB::transaction(function () use ($request, $purchase) {
            foreach ($purchase->items as $oldItem) {
                $this->stockService->deductStock(
                    productId: $oldItem->product_id,
                    storeId: $oldItem->store_id,
                    quantity: (float) $oldItem->quantity,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    createdBy: $request->user()->id,
                    note: "Reversal for editing purchase {$purchase->purchase_id}",
                );
            }

            if ($purchase->paid_amount > 0) {
                $this->cashBankService->credit(
                    amount: $purchase->paid_amount,
                    method: $purchase->payment_method,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    description: "Reversal of payment for purchase {$purchase->purchase_id}",
                    userId: $request->user()->id,
                );
            }

            $purchase->items()->delete();

            [$subtotal, $vatAmount, $total] = $this->calculateTotals($request->items);

            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'invoice_number' => $request->invoice_number,
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $total,
                'paid_amount' => $request->paid_amount ?? 0,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
            ]);

            $this->createItemsAndStock($purchase, $request->items, $request->user()->id);

            if ($purchase->paid_amount > 0) {
                $this->cashBankService->debit(
                    amount: $purchase->paid_amount,
                    method: $purchase->payment_method,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    description: "Payment for updated purchase {$purchase->purchase_id}",
                    userId: $request->user()->id,
                );
            }

            $this->syncPayable($purchase, $request->user()->id);
        });

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('delete', $purchase);

        if ($purchase->returns()->exists()) {
            return redirect()->route('admin.purchases.index')->with('error', 'Cannot delete a purchase that has returns.');
        }

        DB::transaction(function () use ($request, $purchase) {
            foreach ($purchase->items as $item) {
                $this->stockService->deductStock(
                    productId: $item->product_id,
                    storeId: $item->store_id,
                    quantity: (float) $item->quantity,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    createdBy: $request->user()->id,
                    note: "Reversal for deleted purchase {$purchase->purchase_id}",
                );
            }

            if ($purchase->paid_amount > 0) {
                $this->cashBankService->credit(
                    amount: $purchase->paid_amount,
                    method: $purchase->payment_method,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    description: "Reversal of payment for deleted purchase {$purchase->purchase_id}",
                    userId: $request->user()->id,
                );
            }

            Account::where('reference_type', Purchase::class)->where('reference_id', $purchase->id)->delete();

            $purchase->delete();
        });

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase deleted successfully.');
    }

    public function report(Request $request): View
    {
        $purchases = $this->filteredReportQuery($request)->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.purchases.reports.index', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
            'totals' => $this->sumTotals($purchases),
        ]);
    }

    public function reportExcel(Request $request)
    {
        $purchases = $this->filteredReportQuery($request)->get();

        return Excel::download(new PurchasesExport($purchases), 'purchases-report.xlsx');
    }

    public function reportPdf(Request $request)
    {
        $purchases = $this->filteredReportQuery($request)->get();

        $pdf = Pdf::loadView('admin.purchases.reports.pdf', [
            'purchases' => $purchases,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
            'totals' => $this->sumTotals($purchases),
        ]);

        return $pdf->download('purchases-report.pdf');
    }

    private function filteredReportQuery(Request $request)
    {
        $query = Purchase::with('supplier');

        if ($request->from_date) {
            $query->whereDate('purchase_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('purchase_date', '<=', $request->to_date);
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        return $query->latest();
    }

    private function sumTotals($purchases): array
    {
        return [
            'subtotal' => $purchases->sum('subtotal'),
            'vat_amount' => $purchases->sum('vat_amount'),
            'total_amount' => $purchases->sum('total_amount'),
            'paid_amount' => $purchases->sum('paid_amount'),
            'due_amount' => $purchases->sum('due_amount'),
        ];
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0;
        $vatAmount = 0;

        foreach ($items as $item) {
            $price = (float) $item['purchase_price'];
            $qty = (float) $item['quantity'];
            $vatPercentage = (float) ($item['vat_percentage'] ?? 0);

            $lineVat = $vatPercentage > 0 ? ($price * $qty * $vatPercentage / 100) : 0;

            $subtotal += $price * $qty;
            $vatAmount += $lineVat;
        }

        return [$subtotal, $vatAmount, $subtotal + $vatAmount];
    }

    private function syncPayable(Purchase $purchase, int $userId): void
    {
        $existing = Account::where('reference_type', Purchase::class)
            ->where('reference_id', $purchase->id)
            ->first();

        if ($purchase->due_amount <= 0) {
            $existing?->delete();

            return;
        }

        Account::updateOrCreate(
            ['reference_type' => Purchase::class, 'reference_id' => $purchase->id],
            [
                'type' => 'payable',
                'party_type' => 'supplier',
                'party_id' => $purchase->supplier_id,
                'amount' => $purchase->due_amount,
                'description' => "Due for purchase {$purchase->purchase_id}",
                'is_settled' => false,
                'created_by' => $existing?->created_by ?? $userId,
            ]
        );
    }

    private function createItemsAndStock(Purchase $purchase, array $items, int $userId): void
    {
        foreach ($items as $item) {
            $price = (float) $item['purchase_price'];
            $qty = (float) $item['quantity'];
            $vatPercentage = (float) ($item['vat_percentage'] ?? 0);
            $vatAmount = $vatPercentage > 0 ? ($price * $qty * $vatPercentage / 100) : 0;

            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'store_id' => $item['store_id'],
                'quantity' => $qty,
                'purchase_price' => $price,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'total_price' => ($price * $qty) + $vatAmount,
                'expiry_date' => $item['expiry_date'] ?? null,
            ]);

            $this->stockService->addStock(
                productId: $item['product_id'],
                storeId: $item['store_id'],
                quantity: $qty,
                purchasePrice: $price,
                expiryDate: $item['expiry_date'] ?? null,
                referenceType: Purchase::class,
                referenceId: $purchase->id,
                createdBy: $userId,
                note: "Purchase {$purchase->purchase_id}",
            );
        }
    }
}
