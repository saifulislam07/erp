<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ReturnRequest;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\ReturnType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $returns = OrderReturn::with(['order', 'returnType'])
            ->where('client_id', $request->user('client')->id)
            ->latest()
            ->get();

        return view('client.returns.index', compact('returns'));
    }

    public function create(Request $request, Order $order): View
    {
        abort_unless($order->client_id === $request->user('client')->id, 403);
        abort_unless($order->status === 'delivered', 403, 'This order is not eligible for return.');

        $order->load('items.product');

        $rows = $order->items->map(function ($item) {
            $returned = ReturnItem::where('order_item_id', $item->id)
                ->whereHas('orderReturn', fn ($q) => $q->where('status', '!=', 'rejected'))
                ->sum('quantity');

            return (object) [
                'order_item_id' => $item->id,
                'product_name' => $item->product->name,
                'ordered_qty' => $item->quantity,
                'remaining_qty' => $item->quantity - $returned,
                'unit_price' => $item->unit_price,
            ];
        })->filter(fn ($row) => $row->remaining_qty > 0)->values();

        $returnTypes = ReturnType::orderBy('name')->get();

        return view('client.returns.create', compact('order', 'rows', 'returnTypes'));
    }

    public function store(ReturnRequest $request, Order $order): RedirectResponse
    {
        abort_unless($order->client_id === $request->user('client')->id, 403);
        abort_unless($order->status === 'delivered', 403, 'This order is not eligible for return.');

        $return = OrderReturn::create([
            'order_id' => $order->id,
            'client_id' => $request->user('client')->id,
            'return_type_id' => $request->return_type_id,
            'reason' => $request->reason,
            'note' => $request->note,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        foreach ($request->items as $row) {
            $quantity = (float) $row['quantity'];

            if ($quantity <= 0) {
                continue;
            }

            $orderItem = $order->items()->findOrFail($row['order_item_id']);

            $return->items()->create([
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id,
                'quantity' => $quantity,
                'unit_price' => $orderItem->unit_price,
                'total_price' => $quantity * $orderItem->unit_price,
            ]);
        }

        return redirect()->route('client.returns.index')->with('success', 'Return request submitted successfully.');
    }
}
