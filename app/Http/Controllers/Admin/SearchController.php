<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function clients(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $clients = Client::where('status', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('unique_id', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'unique_id', 'name', 'phone', 'type']);

        return response()->json($clients);
    }

    public function products(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $products = Product::with(['category', 'unit', 'stocks'])
            ->where('status', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'unique_id' => $product->unique_id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'unit' => $product->unit?->name,
                'mrp_price' => $product->mrp_price,
                'purchase_price' => $product->purchase_price,
                'sale_price' => $product->sale_price,
                'vat_percentage' => $product->vat_percentage,
                'stock_qty' => $product->stocks->sum('quantity'),
                'expiry_dates' => $product->stocks
                    ->pluck('expiry_date')
                    ->filter()
                    ->map(fn ($date) => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date)
                    ->unique()
                    ->sort()
                    ->values(),
            ]);

        return response()->json($products);
    }

    public function suppliers(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $suppliers = Supplier::where('status', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('unique_id', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'unique_id', 'name', 'phone', 'company_name']);

        return response()->json($suppliers);
    }

    public function orders(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $orders = Order::with('client')
            ->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            })
            ->limit(20)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_id' => $order->order_id,
                'client_name' => $order->client?->name,
                'status' => $order->status,
                'total' => $order->total_amount,
                'date' => $order->created_at?->format('Y-m-d'),
            ]);

        return response()->json($orders);
    }

    public function sales(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $query = Sale::query()
            ->where(function ($q) use ($search) {
                $q->where('sale_id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhereHas('items.product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('sale_date', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('sale_date', '<=', $toDate);
        }

        $sales = $query->limit(20)->get()->map(fn (Sale $sale) => [
            'id' => $sale->id,
            'sale_id' => $sale->sale_id,
            'customer_name' => $sale->customer_name,
            'total_amount' => $sale->total_amount,
            'date' => $sale->sale_date?->format('Y-m-d'),
        ]);

        return response()->json($sales);
    }

    public function returns(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $query = OrderReturn::with('client')
            ->where(function ($q) use ($search) {
                $q->where('return_id', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $returns = $query->limit(20)->get()->map(fn (OrderReturn $return) => [
            'id' => $return->id,
            'return_id' => $return->return_id,
            'client_name' => $return->client?->name,
            'status' => $return->status,
            'date' => $return->created_at?->format('Y-m-d'),
        ]);

        return response()->json($returns);
    }

    public function stocks(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $query = Stock::with(['product.category', 'product.unit'])
            ->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('unique_id', 'like', "%{$search}%");
            });

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $stocks = $query->limit(20)->get()->map(fn (Stock $stock) => [
            'id' => $stock->id,
            'product_id' => $stock->product_id,
            'product_name' => $stock->product?->name,
            'quantity' => $stock->quantity,
            'expiry_date' => $stock->expiry_date,
        ]);

        return response()->json($stocks);
    }

    public function global(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $results = collect();

        Product::where('status', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('unique_id', 'like', "%{$search}%");
            })
            ->limit(5)->get()->each(function (Product $product) use ($results) {
                $results->push([
                    'type' => 'Product',
                    'label' => "{$product->name} ({$product->unique_id})",
                    'url' => route('admin.products.show', $product),
                ]);
            });

        Client::where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('unique_id', 'like', "%{$search}%");
        })->limit(5)->get()->each(function (Client $client) use ($results) {
            $results->push([
                'type' => 'Client',
                'label' => "{$client->name} ({$client->unique_id})",
                'url' => route('admin.clients.edit', $client),
            ]);
        });

        Order::where('order_id', 'like', "%{$search}%")
            ->limit(5)->get()->each(function (Order $order) use ($results) {
                $results->push([
                    'type' => 'Order',
                    'label' => $order->order_id,
                    'url' => route('admin.orders.show', $order),
                ]);
            });

        Sale::where('sale_id', 'like', "%{$search}%")
            ->orWhere('customer_name', 'like', "%{$search}%")
            ->limit(5)->get()->each(function (Sale $sale) use ($results) {
                $results->push([
                    'type' => 'Sale',
                    'label' => "{$sale->sale_id} - {$sale->customer_name}",
                    'url' => route('admin.sales.show', $sale),
                ]);
            });

        Supplier::where('name', 'like', "%{$search}%")
            ->orWhere('unique_id', 'like', "%{$search}%")
            ->limit(5)->get()->each(function (Supplier $supplier) use ($results) {
                $results->push([
                    'type' => 'Supplier',
                    'label' => "{$supplier->name} ({$supplier->unique_id})",
                    'url' => route('admin.suppliers.edit', $supplier),
                ]);
            });

        return response()->json($results->values());
    }
}
