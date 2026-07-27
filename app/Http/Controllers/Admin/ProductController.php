<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['category', 'unit']);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->get()->map(function (Product $product) {
            $product->stock_qty = Schema::hasTable('stocks')
                ? DB::table('stocks')->where('product_id', $product->id)->sum('quantity')
                : 0;

            return $product;
        });

        $categories = Category::topLevel();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = Category::topLevel();
        $units = Unit::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'units'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        $data = $this->applyMrpRestriction($request, $data);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load(['category', 'subCategory', 'unit', 'discounts']);

        $stock = Schema::hasTable('stocks')
            ? DB::table('stocks')->where('product_id', $product->id)->sum('quantity')
            : 0;

        $purchaseHistory = Schema::hasTable('purchase_items')
            ? DB::table('purchase_items')->where('product_id', $product->id)->get()
            : collect();

        $salesHistory = Schema::hasTable('sale_items')
            ? DB::table('sale_items')->where('product_id', $product->id)->get()
            : collect();

        return view('admin.products.show', compact('product', 'stock', 'purchaseHistory', 'salesHistory'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $categories = Category::topLevel();
        $subCategories = $product->category_id
            ? Category::where('parent_id', $product->category_id)->orderBy('name')->get()
            : collect();
        $units = Unit::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'subCategories', 'units'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $data = $this->applyMrpRestriction($request, $data, $product);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $hasActiveOrders = Schema::hasTable('order_items')
            && DB::table('order_items')->where('product_id', $product->id)->exists();

        if ($hasActiveOrders) {
            return redirect()->route('admin.products.index')->with('error', 'Cannot delete a product that has active orders.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $products = Product::with(['category', 'unit'])
            ->where('status', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'unique_id' => $product->unique_id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'unit' => $product->unit?->symbol,
                    'mrp_price' => $product->mrp_price,
                    'purchase_price' => $product->purchase_price,
                    'sale_price' => $product->sale_price,
                    'vat_percentage' => $product->vat_percentage,
                    'stock_qty' => Schema::hasTable('stocks')
                        ? DB::table('stocks')->where('product_id', $product->id)->sum('quantity')
                        : 0,
                ];
            });

        return response()->json($products);
    }

    public function report(Request $request): View
    {
        $products = $this->filteredReportQuery($request)->get();
        $categories = Category::topLevel();
        $category = $request->category_id ? Category::find($request->category_id) : null;

        return view('admin.products.reports.index', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);
    }

    public function reportExcel(Request $request): BinaryFileResponse
    {
        $products = $this->filteredReportQuery($request)->get();

        return Excel::download(new ProductsExport($products), 'products-report.xlsx');
    }

    public function reportPdf(Request $request)
    {
        $products = $this->filteredReportQuery($request)->get();
        $category = $request->category_id ? Category::find($request->category_id) : null;

        $pdf = Pdf::loadView('admin.products.reports.pdf', [
            'products' => $products,
            'category' => $category,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);

        return $pdf->download('products-report.pdf');
    }

    private function filteredReportQuery(Request $request)
    {
        $query = Product::with(['category', 'unit']);

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        return $query->latest();
    }

    private function applyMrpRestriction(Request $request, array $data, ?Product $product = null): array
    {
        if ($request->user()->is_admin) {
            $data['mrp_price'] = $data['mrp_price'] ?? $data['sale_price'];

            return $data;
        }

        $data['mrp_price'] = $product->mrp_price ?? $data['sale_price'];

        return $data;
    }
}
