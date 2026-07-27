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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderReturnController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly CashBankService $cashBankService,
    ) {}

    public function index(Request $request): View
    {
        $query = OrderReturn::with(['order', 'client', 'returnType']);

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

        $returns = $query->latest()->get();

        return view('admin.returns.index', compact('returns'));
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
