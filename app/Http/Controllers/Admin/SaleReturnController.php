<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaleReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\CashBankService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use RuntimeException;

/**
 * Goods coming back from a sale.
 *
 * Recording a return does three things: it puts the stock back (unless the
 * goods are unsellable), it credits the customer for the value returned, and —
 * if money was handed back at the counter — it takes that out of cash/bank.
 */
class SaleReturnController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly CashBankService $cashBank,
    ) {}

    /**
     * Every sale return, across all sales.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeArea();

        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.sales.returns.index');
    }

    /**
     * Server-side DataTables feed for the sale return listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        // As on the purchase return list, the filter bar is the only search, so
        // the header totals below cover exactly the rows being listed.
        $query = SaleReturn::query()
            ->with(['sale', 'items.product', 'creator'])
            ->when($request->get('q'), fn ($q, $search) => $q->where(function ($inner) use ($search) {
                $inner->where('sale_returns.return_id', 'like', "%{$search}%")
                    ->orWhereHas('sale', fn ($s) => $s->where('sale_id', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%"));
            }))
            ->when($request->get('from_date'), fn ($q, $date) => $q->whereDate('return_date', '>=', $date))
            ->when($request->get('to_date'), fn ($q, $date) => $q->whereDate('return_date', '<=', $date))
            ->select('sale_returns.*');

        return DataTables::eloquent($query)
            ->addColumn('returned_on', fn (SaleReturn $return) => $return->return_date?->format('d M Y'))
            ->addColumn('return_link', fn (SaleReturn $return) => view('admin.sales.returns.partials.return-cell', compact('return'))->render())
            ->addColumn('sale_link', fn (SaleReturn $return) => view('admin.sales.returns.partials.sale-cell', compact('return'))->render())
            ->addColumn('customer_name', fn (SaleReturn $return) => e($return->sale?->customer_name ?: 'Walk-in'))
            ->addColumn('items_summary', fn (SaleReturn $return) => view('admin.sales.returns.partials.items-cell', compact('return'))->render())
            ->editColumn('total_amount', fn (SaleReturn $return) => money($return->total_amount))
            ->addColumn('refunded', fn (SaleReturn $return) => view('admin.sales.returns.partials.refund-cell', compact('return'))->render())
            ->addColumn('stock_state', fn (SaleReturn $return) => view('admin.sales.returns.partials.stock-cell', compact('return'))->render())
            ->addColumn('actions', fn (SaleReturn $return) => view('admin.sales.returns.partials.actions', compact('return'))->render())
            ->orderColumn('returned_on', 'return_date $1')
            ->orderColumn('return_link', 'return_id $1')
            ->orderColumn('sale_link', 'sale_id $1')
            ->orderColumn('refunded', 'refund_amount $1')
            ->orderColumn('stock_state', 'restock $1')
            ->with([
                'returned_value' => money((float) $query->clone()->sum('total_amount')),
                'refunded_value' => money((float) $query->clone()->sum('refund_amount')),
            ])
            ->rawColumns(['return_link', 'sale_link', 'items_summary', 'refunded', 'stock_state', 'actions'])
            ->toJson();
    }

    /**
     * Form for returning items from one sale.
     */
    public function create(Sale $sale): View
    {
        $this->authorizeArea();

        $sale->load(['items.product', 'items.store']);

        return view('admin.sales.returns.create', [
            'sale' => $sale,
            'rows' => $this->returnableRows($sale),
        ]);
    }

    public function store(SaleReturnRequest $request, Sale $sale): RedirectResponse
    {
        $this->authorizeArea();

        try {
            $return = DB::transaction(function () use ($request, $sale) {
                $lines = $this->resolveLines($request, $sale);

                if ($lines === []) {
                    throw new RuntimeException('Enter a quantity for at least one item.');
                }

                $total = round(array_sum(array_column($lines, 'total_price')), 2);
                $refund = round((float) $request->input('refund_amount', 0), 2);

                if ($refund > $total) {
                    throw new RuntimeException('The refund cannot exceed the value of the returned goods.');
                }

                $return = SaleReturn::create([
                    'sale_id' => $sale->id,
                    'return_date' => $request->return_date,
                    'reason' => $request->reason,
                    'total_amount' => $total,
                    'refund_amount' => $refund,
                    'refund_method' => $refund > 0 ? $request->refund_method : null,
                    'restock' => $request->boolean('restock'),
                    'created_by' => $request->user()->id,
                ]);

                foreach ($lines as $line) {
                    $return->items()->create($line);

                    if ($return->restock) {
                        $this->stockService->addStock(
                            productId: $line['product_id'],
                            storeId: $line['store_id'],
                            quantity: $line['quantity'],
                            purchasePrice: $line['unit_price'],
                            expiryDate: null,
                            referenceType: SaleReturn::class,
                            referenceId: $return->id,
                            createdBy: $request->user()->id,
                            note: "Sale return {$return->return_id}",
                        );
                    }
                }

                if ($refund > 0) {
                    $this->cashBank->debit(
                        amount: $refund,
                        method: $request->refund_method,
                        referenceType: SaleReturn::class,
                        referenceId: $return->id,
                        description: "Refund for sale return {$return->return_id}",
                        userId: $request->user()->id,
                    );
                }

                return $return;
            });
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.sale-returns.show', $return)
            ->with('success', "Sale return {$return->return_id} recorded.");
    }

    public function show(SaleReturn $saleReturn): View
    {
        $this->authorizeArea();

        $saleReturn->load(['sale.customer', 'items.product', 'creator']);

        return view('admin.sales.returns.show', ['return' => $saleReturn]);
    }

    /**
     * Undo a return: take the restocked goods back out and reverse any refund.
     */
    public function destroy(Request $request, SaleReturn $saleReturn): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403, 'Only an administrator can delete a return.');

        DB::transaction(function () use ($request, $saleReturn) {
            if ($saleReturn->restock) {
                foreach ($saleReturn->items as $item) {
                    $this->stockService->deductStock(
                        productId: $item->product_id,
                        storeId: $item->store_id,
                        quantity: (float) $item->quantity,
                        referenceType: SaleReturn::class,
                        referenceId: $saleReturn->id,
                        createdBy: $request->user()->id,
                        note: "Reversal of deleted return {$saleReturn->return_id}",
                    );
                }
            }

            if ((float) $saleReturn->refund_amount > 0) {
                $this->cashBank->credit(
                    amount: (float) $saleReturn->refund_amount,
                    method: $saleReturn->refund_method ?? 'cash',
                    referenceType: SaleReturn::class,
                    referenceId: $saleReturn->id,
                    description: "Reversal of refund for {$saleReturn->return_id}",
                    userId: $request->user()->id,
                );
            }

            $saleReturn->items()->delete();
            $saleReturn->delete();
        });

        return redirect()
            ->route('admin.sale-returns.index')
            ->with('success', "Return {$saleReturn->return_id} was deleted and its effects reversed.");
    }

    /**
     * Sale lines with quantity still available to return.
     *
     * @return Collection<int, object>
     */
    private function returnableRows(Sale $sale)
    {
        $alreadyReturned = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->whereNull('sale_returns.deleted_at')
            ->whereIn('sale_return_items.sale_item_id', $sale->items->pluck('id'))
            ->groupBy('sale_return_items.sale_item_id')
            ->selectRaw('sale_item_id, SUM(quantity) as total')
            ->pluck('total', 'sale_item_id');

        return $sale->items->map(function ($item) use ($alreadyReturned) {
            $returned = (float) ($alreadyReturned[$item->id] ?? 0);

            // Unit price net of the line discount — returning goods must credit
            // what the customer actually paid for them, not the list price.
            $netUnitPrice = (float) $item->quantity > 0
                ? round(((float) $item->total_price - (float) $item->vat_amount) / (float) $item->quantity, 2)
                : 0.0;

            return (object) [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? 'Deleted product',
                'store_id' => $item->store_id,
                'store_name' => $item->store?->name,
                'sold_qty' => (float) $item->quantity,
                'returned_qty' => $returned,
                'remaining_qty' => round((float) $item->quantity - $returned, 2),
                'unit_price' => $netUnitPrice,
            ];
        })->filter(fn ($row) => $row->remaining_qty > 0)->values();
    }

    /**
     * Turn the submitted rows into return-item payloads, rejecting anything
     * that exceeds what is still returnable.
     *
     * @return list<array<string, mixed>>
     */
    private function resolveLines(SaleReturnRequest $request, Sale $sale): array
    {
        $available = $this->returnableRows($sale)->keyBy('sale_item_id');
        $lines = [];

        foreach ($request->input('items', []) as $row) {
            $quantity = round((float) ($row['quantity'] ?? 0), 2);

            if ($quantity <= 0) {
                continue;
            }

            $source = $available[(int) $row['sale_item_id']] ?? null;

            if (! $source) {
                throw new RuntimeException('One of the selected items does not belong to this sale.');
            }

            if ($quantity > $source->remaining_qty) {
                throw new RuntimeException(
                    "You cannot return {$quantity} of {$source->product_name} — only {$source->remaining_qty} remain."
                );
            }

            $lines[] = [
                'sale_item_id' => $source->sale_item_id,
                'product_id' => $source->product_id,
                'store_id' => $source->store_id,
                'quantity' => $quantity,
                'unit_price' => $source->unit_price,
                'total_price' => round($quantity * $source->unit_price, 2),
            ];
        }

        return $lines;
    }

    private function authorizeArea(): void
    {
        abort_unless(
            auth()->user()->is_admin || auth()->user()->can('sale.view'),
            403,
        );
    }
}
