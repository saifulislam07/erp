<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData();
        }

        return view('admin.departments.index');
    }

    /**
     * Server-side DataTables feed for the department listing.
     */
    protected function indexData(): JsonResponse
    {
        return DataTables::eloquent(Department::query()->withCount('users'))
            ->addIndexColumn()
            ->editColumn('description', fn (Department $department) => e($department->description ?: '-'))
            ->addColumn('created_on', fn (Department $department) => $department->created_at?->format('Y-m-d'))
            ->addColumn('actions', fn (Department $department) => view('admin.departments.partials.actions', compact('department'))->render())
            ->orderColumn('created_on', 'created_at $1')
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());

        return redirect()->route('admin.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Department deleted successfully.');
    }
}
