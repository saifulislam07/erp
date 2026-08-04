<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderPackRequest;
use App\Http\Requests\Admin\OrderRejectRequest;
use App\Http\Requests\Admin\OrderUpdateStatusRequest;
use App\Models\Order;
use App\Models\Packaging;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    // 'confirmed' -> 'on_delivery' happens via the Store dispatch queue, and
    // 'on_delivery' -> 'delivered' happens via the Delivery workflow (Phase 9).
    private const array TRANSITIONS = [
        'processing' => 'confirmed',
    ];

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.orders.index');
    }

    /**
     * Server-side DataTables feed for the order listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        $query = Order::query()->with('client')->select('orders.*');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($orderId = $request->get('order_id')) {
            $query->where('order_id', 'like', "%{$orderId}%");
        }

        if ($clientName = $request->get('client_name')) {
            $query->whereHas('client', fn ($q) => $q->where('name', 'like', "%{$clientName}%"));
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
                        $q->where('orders.order_id', 'like', "%{$search}%")
                            ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%")
                                ->orWhere('unique_id', 'like', "%{$search}%"));
                    });
                }
            }, true)
            ->addColumn('client_label', fn (Order $order) => $order->client
                ? e($order->client->name).' ('.e($order->client->unique_id).')'
                : '—')
            ->addColumn('placed_on', fn (Order $order) => $order->created_at?->format('Y-m-d'))
            ->addColumn('state', fn (Order $order) => view('admin.orders.partials.status-cell', compact('order'))->render())
            ->addColumn('actions', fn (Order $order) => view('admin.orders.partials.actions', compact('order'))->render())
            ->orderColumn('client_label', 'client_id $1')
            ->orderColumn('placed_on', 'created_at $1')
            ->orderColumn('state', 'status $1')
            ->rawColumns(['client_label', 'state', 'actions'])
            ->toJson();
    }

    public function pending(): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::with('client')->where('status', 'pending')->latest()->get();

        return view('admin.orders.pending', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['client', 'items.product', 'statusLogs', 'packaging', 'feedbacks']);
        $nextStatus = self::TRANSITIONS[$order->status] ?? null;

        return view('admin.orders.show', compact('order', 'nextStatus'));
    }

    public function accept(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be accepted.');
        }

        $order->update(['status' => 'processing']);
        $order->logStatusChange('pending', 'processing', 'admin', $request->user()->id, 'Order accepted by admin.');

        $order->client->notify(new OrderStatusChangedNotification($order));

        return back()->with('success', 'Order accepted.');
    }

    public function reject(OrderRejectRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be rejected.');
        }

        $order->update(['status' => 'rejected', 'admin_note' => $request->reason]);
        $order->logStatusChange('pending', 'rejected', 'admin', $request->user()->id, $request->reason);

        $order->client->notify(new OrderStatusChangedNotification($order, $request->reason));

        return back()->with('success', 'Order rejected.');
    }

    public function updateStatus(OrderUpdateStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $expectedNext = self::TRANSITIONS[$order->status] ?? null;

        if ($expectedNext === null || $request->status !== $expectedNext) {
            return back()->with('error', "Order cannot move from {$order->status} to {$request->status}.");
        }

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);
        $order->logStatusChange($oldStatus, $request->status, 'admin', $request->user()->id);

        if ($request->status === 'confirmed') {
            Packaging::firstOrCreate(
                ['order_id' => $order->id],
                ['packed_by' => $request->user()->id, 'packed_at' => now()]
            );
        }

        $order->client->notify(new OrderStatusChangedNotification($order));

        return back()->with('success', 'Order status updated.');
    }

    public function pack(OrderPackRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if (! in_array($order->status, ['confirmed', 'on_delivery', 'delivered'])) {
            return back()->with('error', 'Order must be confirmed before it can be packed.');
        }

        Packaging::updateOrCreate(
            ['order_id' => $order->id],
            ['packed_by' => $request->user()->id, 'packed_at' => now(), 'notes' => $request->notes]
        );

        return back()->with('success', 'Packaging details recorded.');
    }
}
