<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryFailedRequest;
use App\Http\Requests\Admin\DeliveryOutRequest;
use App\Models\Delivery;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $query = Delivery::with(['order.client']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $deliveries = $query->latest()->get();

        return view('admin.deliveries.index', compact('deliveries'));
    }

    public function out(DeliveryOutRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('dispatch', $delivery->order);

        if ($delivery->status !== 'pending') {
            return back()->with('error', 'Only pending deliveries can be marked out for delivery.');
        }

        $delivery->update([
            'status' => 'out_for_delivery',
            'delivery_person_name' => $request->delivery_person_name,
            'delivery_date' => now()->toDateString(),
        ]);

        if ($delivery->order->status !== 'on_delivery') {
            $oldStatus = $delivery->order->status;
            $delivery->order->update(['status' => 'on_delivery']);
            $delivery->order->logStatusChange($oldStatus, 'on_delivery', 'admin', $request->user()->id);
        }

        return back()->with('success', 'Delivery marked as out for delivery.');
    }

    public function delivered(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('dispatch', $delivery->order);

        if ($delivery->status !== 'out_for_delivery') {
            return back()->with('error', 'Only deliveries that are out for delivery can be marked delivered.');
        }

        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $order = $delivery->order;
        $oldStatus = $order->status;
        $order->update(['status' => 'delivered']);
        $order->logStatusChange($oldStatus, 'delivered', 'admin', $request->user()->id, 'Delivery completed.');

        $order->client->notify(new OrderStatusChangedNotification($order));

        return back()->with('success', 'Delivery marked as delivered.');
    }

    public function failed(DeliveryFailedRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('dispatch', $delivery->order);

        $delivery->update([
            'status' => 'failed',
            'delivery_note' => $request->delivery_note,
        ]);

        $order = $delivery->order;
        $oldStatus = $order->status;
        $order->update(['status' => 'processing']);
        $order->logStatusChange($oldStatus, 'processing', 'admin', $request->user()->id, "Delivery failed: {$request->delivery_note}");

        $order->client->notify(new OrderStatusChangedNotification($order, "Delivery attempt failed: {$request->delivery_note}"));

        return back()->with('success', 'Delivery marked as failed. Order reverted to processing.');
    }
}
