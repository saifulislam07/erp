<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockAdjustRequest;
use App\Http\Requests\Admin\StockRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function index(Request $request): View
    {
        $query = Stock::with(['product.category', 'product.unit', 'store'])
            ->where('quantity', '>', 0);

        if ($search = $request->get('q')) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('unique_id', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->get('category_id')) {
            $query->whereHas('product', fn ($q) => $q->where('category_id', $categoryId));
        }

        if ($storeId = $request->get('store_id')) {
            $query->where('store_id', $storeId);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $stocks = $query->latest()->get();
        $categories = Category::topLevel();
        $stores = Store::orderBy('name')->get();

        return view('admin.stocks.index', compact('stocks', 'categories', 'stores'));
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        $stores = Store::where('status', true)->orderBy('name')->get();

        return view('admin.stocks.create', compact('products', 'stores'));
    }

    public function store(StockRequest $request): RedirectResponse
    {
        $this->stockService->addStock(
            productId: $request->product_id,
            storeId: $request->store_id,
            quantity: $request->quantity,
            purchasePrice: $request->purchase_price,
            expiryDate: $request->expiry_date,
            referenceType: null,
            referenceId: null,
            createdBy: $request->user()->id,
            note: 'Manual stock addition',
            batchNumber: $request->batch_number,
        );

        return redirect()->route('admin.stocks.index')->with('success', 'Stock added successfully.');
    }

    public function edit(Stock $stock): View
    {
        return view('admin.stocks.edit', compact('stock'));
    }

    public function update(StockAdjustRequest $request, Stock $stock): RedirectResponse
    {
        $before = $this->stockService->getAvailableStock($stock->product_id, $stock->store_id);

        $delta = $request->quantity - $stock->quantity;
        $stock->update(['quantity' => $request->quantity]);

        $after = $before + $delta;

        $this->stockService->logMovement(
            productId: $stock->product_id,
            storeId: $stock->store_id,
            movementType: 'adjustment',
            referenceType: null,
            referenceId: null,
            quantity: abs($delta),
            beforeQuantity: $before,
            afterQuantity: $after,
            note: $request->reason,
            createdBy: $request->user()->id,
        );

        $this->stockService->checkLowStock($stock->product_id);

        return redirect()->route('admin.stocks.index')->with('success', 'Stock adjusted successfully.');
    }

    public function destroy(Request $request, Stock $stock): RedirectResponse
    {
        $before = $this->stockService->getAvailableStock($stock->product_id, $stock->store_id);
        $removedQuantity = (float) $stock->quantity;

        $this->stockService->logMovement(
            productId: $stock->product_id,
            storeId: $stock->store_id,
            movementType: 'adjustment',
            referenceType: null,
            referenceId: null,
            quantity: $removedQuantity,
            beforeQuantity: $before,
            afterQuantity: $before - $removedQuantity,
            note: 'Stock entry removed',
            createdBy: $request->user()->id,
        );

        $stock->delete();

        return redirect()->route('admin.stocks.index')->with('success', 'Stock entry removed successfully.');
    }

    public function lowQuantity(): View
    {
        $products = Product::with(['category', 'unit'])
            ->get()
            ->map(function (Product $product) {
                $product->total_stock = $this->stockService->getAvailableStock($product->id);

                return $product;
            })
            ->filter(fn (Product $product) => $product->total_stock < $product->min_stock_threshold)
            ->values();

        return view('admin.stocks.low-quantity', compact('products'));
    }

    public function expiryOneMonth(): View
    {
        return $this->expiryList(30, 'One Month');
    }

    public function expiryThreeMonth(): View
    {
        return $this->expiryList(90, 'Three Months');
    }

    private function expiryList(int $days, string $label): View
    {
        $stocks = Stock::with(['product.category', 'product.unit', 'store'])
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->orderBy('expiry_date')
            ->get();

        return view('admin.stocks.expiry', compact('stocks', 'label'));
    }
}
