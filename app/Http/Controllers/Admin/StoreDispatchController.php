<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Store;
use App\Models\StoreDispatchLog;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreDispatchController extends Controller
{
    public function dispatchQueue(): View
    {
        $orders = Order::with('client')
            ->where('status', 'confirmed')
            ->whereDoesntHave('dispatchLog')
            ->latest()
            ->get();

        $stores = Store::where('status', true)->orderBy('name')->get();

        return view('admin.store.dispatch-queue', compact('orders', 'stores'));
    }

    public function dispatch(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'confirmed' || $order->dispatchLog()->exists()) {
            return back()->with('error', 'This order cannot be dispatched.');
        }

        $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'delivery_note' => ['nullable', 'string'],
        ]);

        $dispatchLog = StoreDispatchLog::create([
            'order_id' => $order->id,
            'store_id' => $request->store_id,
            'dispatched_by' => $request->user()->id,
            'dispatched_at' => now(),
            'delivery_note' => $request->delivery_note,
        ]);

        Delivery::create([
            'order_id' => $order->id,
            'store_dispatch_log_id' => $dispatchLog->id,
            'status' => 'pending',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => 'on_delivery']);
        $order->logStatusChange($oldStatus, 'on_delivery', 'admin', $request->user()->id, 'Order dispatched from store.');

        $order->client->notify(new OrderStatusChangedNotification($order));

        return redirect()->route('admin.store.dispatch-queue')->with('success', 'Order dispatched successfully.');
    }
}
