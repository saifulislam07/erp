<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StoreController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData();
        }

        return view('admin.stores.index');
    }

    /**
     * Server-side DataTables feed for the store listing.
     */
    protected function indexData(): JsonResponse
    {
        return DataTables::eloquent(Store::query()->withCount('stocks'))
            ->addIndexColumn()
            ->editColumn('location', fn (Store $store) => e($store->location ?: '-'))
            ->addColumn('state', fn (Store $store) => view('admin.stores.partials.status-cell', compact('store'))->render())
            ->addColumn('actions', fn (Store $store) => view('admin.stores.partials.actions', compact('store'))->render())
            ->orderColumn('state', 'status $1')
            ->rawColumns(['state', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('admin.stores.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Store::create($request->validated());

        return redirect()->route('admin.stores.index')->with('success', 'Store created successfully.');
    }

    public function edit(Store $store): View
    {
        return view('admin.stores.edit', compact('store'));
    }

    public function update(StoreRequest $request, Store $store): RedirectResponse
    {
        $store->update($request->validated());

        return redirect()->route('admin.stores.index')->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        if ($store->stocks()->where('quantity', '>', 0)->exists()) {
            return redirect()->route('admin.stores.index')->with('error', 'Cannot delete a store that still holds stock.');
        }

        $store->delete();

        return redirect()->route('admin.stores.index')->with('success', 'Store deleted successfully.');
    }
}
