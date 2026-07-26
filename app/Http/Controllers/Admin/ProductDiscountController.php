<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductDiscountRequest;
use App\Models\Product;
use App\Models\ProductDiscount;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductDiscountController extends Controller
{
    public function index(Product $product): View
    {
        $discounts = $product->discounts()->latest()->get();

        return view('admin.products.discounts.index', compact('product', 'discounts'));
    }

    public function create(Product $product): View
    {
        return view('admin.products.discounts.create', compact('product'));
    }

    public function store(ProductDiscountRequest $request, Product $product): RedirectResponse
    {
        $product->discounts()->create($request->validated());

        return redirect()->route('admin.products.discounts.index', $product)->with('success', 'Discount created successfully.');
    }

    public function edit(Product $product, ProductDiscount $discount): View
    {
        return view('admin.products.discounts.edit', compact('product', 'discount'));
    }

    public function update(ProductDiscountRequest $request, Product $product, ProductDiscount $discount): RedirectResponse
    {
        $discount->update($request->validated());

        return redirect()->route('admin.products.discounts.index', $product)->with('success', 'Discount updated successfully.');
    }

    public function destroy(Product $product, ProductDiscount $discount): RedirectResponse
    {
        $discount->delete();

        return redirect()->route('admin.products.discounts.index', $product)->with('success', 'Discount deleted successfully.');
    }
}
