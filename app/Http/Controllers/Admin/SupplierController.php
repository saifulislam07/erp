<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.suppliers.index');
    }

    /**
     * Server-side DataTables feed for the supplier listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        return DataTables::eloquent(Supplier::query())
            ->filter(function ($query) use ($request) {
                $search = $request->get('q') ?: $request->input('search.value');

                if (filled($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('unique_id', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
                }
            }, true)
            ->editColumn('company_name', fn (Supplier $supplier) => e($supplier->company_name ?: '-'))
            ->addColumn('state', fn (Supplier $supplier) => view('admin.suppliers.partials.status-cell', compact('supplier'))->render())
            ->addColumn('actions', fn (Supplier $supplier) => view('admin.suppliers.partials.actions', compact('supplier'))->render())
            ->orderColumn('state', 'status $1')
            ->rawColumns(['state', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->route('admin.suppliers.index')->with('error', 'Cannot delete a supplier with purchase history.');
        }

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
