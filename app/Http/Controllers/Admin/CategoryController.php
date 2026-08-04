<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData();
        }

        return view('admin.categories.index');
    }

    /**
     * Server-side DataTables feed for the category listing.
     */
    protected function indexData(): JsonResponse
    {
        $query = Category::query()->with('parent')->withCount('products')->select('categories.*');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('parent_name', fn (Category $category) => e($category->parent?->name ?? '-'))
            ->addColumn('state', fn (Category $category) => view('admin.categories.partials.status-cell', compact('category'))->render())
            ->addColumn('actions', fn (Category $category) => view('admin.categories.partials.actions', compact('category'))->render())
            ->filterColumn('parent_name', fn ($query, $keyword) => $query->whereHas('parent', fn ($p) => $p->where('name', 'like', "%{$keyword}%")))
            ->orderColumn('parent_name', 'parent_id $1')
            ->orderColumn('state', 'status $1')
            ->rawColumns(['state', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        $parents = Category::topLevel();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $hasProducts = $category->products()->exists()
            || Product::where('sub_category_id', $category->id)->exists();

        if ($hasProducts) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete a category that has products.');
        }

        if ($category->children()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete a category that has sub-categories.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function subcategories(Category $category): JsonResponse
    {
        return response()->json(
            $category->children()->where('status', true)->orderBy('name')->get(['id', 'name'])
        );
    }
}
