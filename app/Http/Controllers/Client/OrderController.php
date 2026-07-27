<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\FeedbackRequest;
use App\Http\Requests\Client\OrderCancelRequest;
use App\Http\Requests\Client\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\User;
use App\Notifications\OrderSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const DELIVERY_CHARGE = 60.00;

    public function index(Request $request): View
    {
        $client = $request->user('client');

        $query = $client->orders();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = $query->latest()->get();

        return view('client.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $products = Product::with(['category', 'unit'])->where('status', true)->orderBy('name')->get();

        return view('client.orders.create', compact('products'));
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $client = $request->user('client');

        $order = DB::transaction(function () use ($request, $client) {
            $subtotal = 0;
            $discountAmount = 0;
            $vatAmount = 0;
            $itemsData = [];

            foreach ($request->items as $row) {
                $product = Product::findOrFail($row['product_id']);
                $qty = (float) $row['quantity'];
                $price = (float) $product->sale_price;
                $lineSubtotal = $price * $qty;

                $discount = $this->calculateActiveDiscount($product, $lineSubtotal);
                $vat = $product->vat_percentage > 0 ? ($price * $qty * $product->vat_percentage / 100) : 0;

                $subtotal += $lineSubtotal;
                $discountAmount += $discount;
                $vatAmount += $vat;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_amount' => $discount,
                    'vat_amount' => $vat,
                    'total_price' => $lineSubtotal - $discount + $vat,
                ];
            }

            $paymentReceiptPath = null;

            if ($request->hasFile('payment_receipt')) {
                $paymentReceiptPath = app(\App\Services\MediaService::class)->store($request->file('payment_receipt'), 'payment-receipts');
            }

            $totalAmount = $subtotal - $discountAmount + $vatAmount + self::DELIVERY_CHARGE;

            $order = Order::create([
                'client_id' => $client->id,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'transaction_reference' => $request->transaction_reference,
                'payment_receipt' => $paymentReceiptPath,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'delivery_charge' => self::DELIVERY_CHARGE,
                'note' => $request->note,
                'created_by' => $client->id,
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            $order->logStatusChange(null, 'pending', 'client', $client->id, 'Order submitted by client.');

            return $order;
        });

        $admins = User::where('is_admin', true)->get();
        Notification::send($admins, new OrderSubmittedNotification($order));

        return redirect()->route('client.orders.show', $order)->with('success', 'Order submitted successfully.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->client_id === $request->user('client')->id, 403);

        $order->load(['items.product', 'statusLogs', 'feedbacks']);

        return view('client.orders.show', compact('order'));
    }

    public function cancel(OrderCancelRequest $request, Order $order): RedirectResponse
    {
        abort_unless($order->client_id === $request->user('client')->id, 403);

        if (! in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $oldStatus = $order->status;

        $order->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        $order->logStatusChange($oldStatus, 'cancelled', 'client', $request->user('client')->id, $request->cancel_reason);

        return redirect()->route('client.orders.show', $order)->with('success', 'Order cancelled.');
    }

    public function feedback(FeedbackRequest $request, Order $order): RedirectResponse
    {
        $client = $request->user('client');

        abort_unless($order->client_id === $client->id, 403);

        if ($order->status !== 'delivered') {
            return back()->with('error', 'Feedback can only be submitted for delivered orders.');
        }

        foreach (['product', 'delivery', 'agent'] as $type) {
            $rating = $request->input("{$type}_rating");

            if (! $rating) {
                continue;
            }

            $order->feedbacks()->updateOrCreate(
                ['client_id' => $client->id, 'type' => $type],
                ['rating' => $rating, 'comment' => $request->input("{$type}_comment")]
            );
        }

        return redirect()->route('client.orders.show', $order)->with('success', 'Thank you for your feedback.');
    }

    private function calculateActiveDiscount(Product $product, float $lineSubtotal): float
    {
        $discount = ProductDiscount::where('product_id', $product->id)
            ->where('status', true)
            ->whereIn('applicable_to', ['all', 'client_agent'])
            ->whereDate('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now());
            })
            ->first();

        if (! $discount) {
            return 0;
        }

        return $discount->discount_type === 'percentage'
            ? $lineSubtotal * $discount->discount_value / 100
            : min($discount->discount_value, $lineSubtotal);
    }
}
