<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReturnApproveRequest;
use App\Http\Requests\Admin\ReturnRejectRequest;
use App\Models\DamageLog;
use App\Models\OrderReturn;
use App\Models\Store;
use App\Services\CashBankService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class OrderReturnController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly CashBankService $cashBankService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.returns.index');
    }

    /**
     * Server-side DataTables feed for the return listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        $query = OrderReturn::query()->with(['order', 'client', 'returnType'])->select('order_returns.*');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($clientName = $request->get('client_name')) {
            $query->whereHas('client', fn ($q) => $q->where('name', 'like', "%{$clientName}%"));
        }

        if ($returnId = $request->get('return_id')) {
            $query->where('return_id', 'like', "%{$returnId}%");
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (filled($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('order_returns.return_id', 'like', "%{$search}%")
                            ->orWhereHas('order', fn ($o) => $o->where('order_id', 'like', "%{$search}%"))
                            ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                    });
                }
            }, true)
            ->addColumn('order_label', fn (OrderReturn $return) => e($return->order?->order_id ?? '—'))
            ->addColumn('client_name', fn (OrderReturn $return) => e($return->client?->name ?? '—'))
            ->addColumn('type_name', fn (OrderReturn $return) => e($return->returnType?->name ?? '—'))
            ->addColumn('state', fn (OrderReturn $return) => view('admin.returns.partials.status-cell', compact('return'))->render())
            ->addColumn('refund', fn (OrderReturn $return) => $return->refund_amount ?? '-')
            ->addColumn('actions', fn (OrderReturn $return) => view('admin.returns.partials.actions', compact('return'))->render())
            ->orderColumn('order_label', 'order_id $1')
            ->orderColumn('client_name', 'client_id $1')
            ->orderColumn('type_name', 'return_type_id $1')
            ->orderColumn('state', 'status $1')
            ->orderColumn('refund', 'refund_amount $1')
            ->rawColumns(['state', 'actions'])
            ->toJson();
    }

    public function show(OrderReturn $return): View
    {
        $return->load(['order', 'client', 'returnType', 'items.product']);

        return view('admin.returns.show', compact('return'));
    }

    public function approve(ReturnApproveRequest $request, OrderReturn $return): RedirectResponse
    {
        if ($return->status !== 'pending') {
            return back()->with('error', 'This return has already been processed.');
        }

        DB::transaction(function () use ($request, $return) {
            $return->load(['items', 'order.dispatchLog', 'returnType']);

            $storeId = $return->order->dispatchLog?->store_id ?? Store::where('status', true)->value('id');

            foreach ($return->items as $item) {
                if ($return->returnType->disposition === 'restock' && $storeId) {
                    $this->stockService->addStock(
                        productId: $item->product_id,
                        storeId: $storeId,
                        quantity: (float) $item->quantity,
                        purchasePrice: $item->product->purchase_price ?? 0,
                        expiryDate: null,
                        referenceType: OrderReturn::class,
                        referenceId: $return->id,
                        createdBy: $request->user()->id,
                        note: "Restocked from return {$return->return_id}",
                    );
                } else {
                    DamageLog::create([
                        'return_id' => $return->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'reason' => $return->reason,
                        'logged_at' => now(),
                    ]);
                }
            }

            $return->update([
                'status' => 'approved',
                'refund_amount' => $request->refund_amount,
                'refund_method' => $request->refund_method,
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ]);

            $this->cashBankService->debit(
                amount: (float) $request->refund_amount,
                method: $request->refund_method,
                referenceType: OrderReturn::class,
                referenceId: $return->id,
                description: "Refund for return {$return->return_id}",
                userId: $request->user()->id,
            );
        });

        return redirect()->route('admin.returns.show', $return)->with('success', 'Return approved successfully.');
    }

    public function reject(ReturnRejectRequest $request, OrderReturn $return): RedirectResponse
    {
        if ($return->status !== 'pending') {
            return back()->with('error', 'This return has already been processed.');
        }

        $return->update([
            'status' => 'rejected',
            'note' => $request->note,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.returns.show', $return)->with('success', 'Return rejected.');
    }
}
