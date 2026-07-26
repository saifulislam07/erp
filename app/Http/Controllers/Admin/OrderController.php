<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Packaging;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    // 'confirmed' -> 'on_delivery' happens via the Store dispatch queue, and
    // 'on_delivery' -> 'delivered' happens via the Delivery workflow (Phase 9).
    private const array TRANSITIONS = [
        'processing' => 'confirmed',
    ];

    public function index(Request $request): View
    {
        $query = Order::with('client');

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

        $orders = $query->latest()->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function pending(): View
    {
        $orders = Order::with('client')->where('status', 'pending')->latest()->get();

        return view('admin.orders.pending', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['client', 'items.product', 'statusLogs', 'packaging', 'feedbacks']);
        $nextStatus = self::TRANSITIONS[$order->status] ?? null;

        return view('admin.orders.show', compact('order', 'nextStatus'));
    }

    public function accept(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be accepted.');
        }

        $order->update(['status' => 'processing']);
        $order->logStatusChange('pending', 'processing', 'admin', $request->user()->id, 'Order accepted by admin.');

        $order->client->notify(new OrderStatusChangedNotification($order));

        return back()->with('success', 'Order accepted.');
    }

    public function reject(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be rejected.');
        }

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $order->update(['status' => 'rejected', 'admin_note' => $request->reason]);
        $order->logStatusChange('pending', 'rejected', 'admin', $request->user()->id, $request->reason);

        $order->client->notify(new OrderStatusChangedNotification($order, $request->reason));

        return back()->with('success', 'Order rejected.');
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(array_values(self::TRANSITIONS))],
        ]);

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

    public function pack(Request $request, Order $order): RedirectResponse
    {
        if (! in_array($order->status, ['confirmed', 'on_delivery', 'delivered'])) {
            return back()->with('error', 'Order must be confirmed before it can be packed.');
        }

        $request->validate(['notes' => ['nullable', 'string']]);

        Packaging::updateOrCreate(
            ['order_id' => $order->id],
            ['packed_by' => $request->user()->id, 'packed_at' => now(), 'notes' => $request->notes]
        );

        return back()->with('success', 'Packaging details recorded.');
    }
}
