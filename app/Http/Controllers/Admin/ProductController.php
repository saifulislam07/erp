<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Services\MediaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.products.index', ['categories' => Category::topLevel()]);
    }

    /**
     * Server-side DataTables feed for the product listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        // `discounts` is eager loaded because every row asks the model for its
        // running discount; without it the listing issues one query per product.
        $query = Product::query()
            ->with(['category', 'subCategory', 'unit', 'discounts'])
            ->select('products.*');

        // Stock arrives as a correlated subquery so the column stays sortable
        // in SQL instead of being stitched on after the page was fetched.
        if (Schema::hasTable('stocks')) {
            $query->addSelect(['stock_qty' => DB::table('stocks')
                ->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('product_id', 'products.id'),
            ]);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                // The filter bar's `q` and the DataTables search box share one
                // implementation so both narrow the list the same way.
                $search = $request->get('q') ?: $request->input('search.value');

                if (filled($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('products.name', 'like', "%{$search}%")
                            ->orWhere('products.unique_id', 'like', "%{$search}%");
                    });
                }
            }, true)
            ->editColumn('purchase_price', fn (Product $product) => money($product->purchase_price))
            ->addColumn('thumb', fn (Product $product) => view('admin.products.partials.thumb', compact('product'))->render())
            ->addColumn('product', fn (Product $product) => view('admin.products.partials.name-cell', compact('product'))->render())
            ->addColumn('category_name', fn (Product $product) => view('admin.products.partials.category-cell', compact('product'))->render())
            ->addColumn('unit_name', fn (Product $product) => e($product->unit?->name ?? '—'))
            ->addColumn('price', fn (Product $product) => view('admin.products.partials.price-cell', compact('product'))->render())
            ->addColumn('stock', fn (Product $product) => qty((float) ($product->stock_qty ?? 0)))
            ->addColumn('state', fn (Product $product) => view('admin.products.partials.status-cell', compact('product'))->render())
            ->addColumn('actions', fn (Product $product) => view('admin.products.partials.actions', compact('product'))->render())
            ->orderColumn('category_name', 'category_id $1')
            ->orderColumn('unit_name', 'unit_id $1')
            ->orderColumn('price', 'sale_price $1')
            ->orderColumn('stock', 'stock_qty $1')
            ->orderColumn('state', 'status $1')
            ->rawColumns(['thumb', 'product', 'category_name', 'price', 'state', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = Category::topLevel();
        $subCategories = old('category_id')
            ? Category::where('parent_id', old('category_id'))->orderBy('name')->get()
            : collect();
        $units = Unit::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'subCategories', 'units'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        $data = $this->applyMrpRestriction($request, $data);
        unset($data['images'], $data['primary_image']);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);
            $this->syncImages($request, $product);

            return $product;
        });

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load(['category', 'subCategory', 'unit', 'discounts', 'images']);

        $stockByStore = Schema::hasTable('stocks')
            ? DB::table('stocks')
                ->leftJoin('stores', 'stores.id', '=', 'stocks.store_id')
                ->where('stocks.product_id', $product->id)
                ->groupBy('stores.id', 'stores.name')
                ->selectRaw('stores.name as store_name, SUM(stocks.quantity) as quantity')
                ->get()
            : collect();

        $stock = (float) $stockByStore->sum('quantity');

        $purchaseHistory = Schema::hasTable('purchase_items')
            ? DB::table('purchase_items')
                ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->where('purchase_items.product_id', $product->id)
                ->whereNull('purchases.deleted_at')
                ->orderByDesc('purchases.purchase_date')
                ->limit(10)
                ->get([
                    'purchases.id',
                    'purchases.purchase_id as reference',
                    'purchases.purchase_date as dated_on',
                    'suppliers.name as party_name',
                    'purchase_items.quantity',
                    'purchase_items.purchase_price as unit_price',
                    'purchase_items.total_price',
                ])
            : collect();

        $salesHistory = Schema::hasTable('sale_items')
            ? DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $product->id)
                ->whereNull('sales.deleted_at')
                ->orderByDesc('sales.sale_date')
                ->limit(10)
                ->get([
                    'sales.id',
                    'sales.sale_id as reference',
                    'sales.sale_date as dated_on',
                    'sales.customer_name as party_name',
                    'sale_items.quantity',
                    'sale_items.unit_price',
                    'sale_items.total_price',
                ])
            : collect();

        return view('admin.products.show', compact(
            'product', 'stock', 'stockByStore', 'purchaseHistory', 'salesHistory'
        ));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load('images');

        $categories = Category::topLevel();
        $subCategories = Category::where('parent_id', old('category_id', $product->category_id))
            ->orderBy('name')
            ->get();
        $units = Unit::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'subCategories', 'units'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $data = $this->applyMrpRestriction($request, $data, $product);
        unset($data['images'], $data['primary_image']);

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data);
            $this->removeImages($request, $product);
            $this->syncImages($request, $product);
        });

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
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

    /**
     * Kept as an alias so existing AJAX callers of /admin/products/search keep
     * working. The canonical implementation lives in SearchController.
     */
    public function search(Request $request, SearchController $search): JsonResponse
    {
        return $search->products($request);
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

    /**
     * Store newly uploaded gallery images and settle which one is primary.
     *
     * The chosen primary is mirrored onto `products.image` so listings, order
     * screens and the client portal can show a thumbnail without a join.
     */
    private function syncImages(Request $request, Product $product): void
    {
        $nextOrder = (int) $product->images()->max('sort_order');

        foreach ($request->file('images', []) as $file) {
            $product->images()->create([
                'path' => $this->media->storeImage($file, 'products'),
                'sort_order' => ++$nextOrder,
            ]);
        }

        $this->applyPrimaryImage($request, $product);
    }

    /**
     * Delete the gallery images the user ticked for removal.
     */
    private function removeImages(Request $request, Product $product): void
    {
        $ids = array_filter((array) $request->input('remove_images', []));

        if ($ids === []) {
            return;
        }

        // Constrained to this product so a crafted request cannot delete
        // another product's images.
        $product->images()->whereIn('id', $ids)->get()
            ->each(fn (ProductImage $image) => $image->delete());
    }

    /**
     * Mark one gallery row primary — the one the user picked when it still
     * exists, otherwise the oldest remaining image.
     */
    private function applyPrimaryImage(Request $request, Product $product): void
    {
        $product->load('images');

        $chosen = $product->images->firstWhere('id', (int) $request->input('primary_image'))
            ?? $product->images->firstWhere('is_primary', true)
            ?? $product->images->first();

        $product->images()->where('is_primary', true)->update(['is_primary' => false]);

        if ($chosen) {
            $chosen->forceFill(['is_primary' => true])->save();
        }

        $product->forceFill(['image' => $chosen?->path])->save();
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
