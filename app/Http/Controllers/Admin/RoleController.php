<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Permission modules shown as "menu access" groups on the role form,
     * mirroring the sidebar sections gated by these permission prefixes.
     */
    private const array MODULES = [
        'department' => 'Departments',
        'role' => 'Roles',
        'user' => 'Employees',
        'client' => 'Clients / Agents',
        'product' => 'Products',
        'stock' => 'Stock',
        'purchase' => 'Purchases',
        'sale' => 'Sales',
        'order' => 'Orders',
        'delivery' => 'Store & Delivery',
        'return' => 'Returns & Feedback',
        'message' => 'Messages',
        'expense' => 'Expenses',
        'cash' => 'Cash & Bank',
        'asset' => 'Assets',
        'report' => 'Reports',
        'invoice' => 'Invoices',
    ];

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData();
        }

        return view('admin.roles.index');
    }

    /**
     * Server-side DataTables feed for the role listing.
     */
    protected function indexData(): JsonResponse
    {
        return DataTables::eloquent(Role::query()->withCount(['permissions', 'users']))
            ->addIndexColumn()
            ->addColumn('created_on', fn (Role $role) => $role->created_at?->format('Y-m-d'))
            ->addColumn('actions', fn (Role $role) => view('admin.roles.partials.actions', compact('role'))->render())
            ->orderColumn('created_on', 'created_at $1')
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        $groupedPermissions = $this->groupedPermissions();

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions') ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $groupedPermissions = $this->groupedPermissions();
        $assignedPermissions = $role->permissions()->pluck('name')->all();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'assignedPermissions'));
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($request->validated('permissions') ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete a role that still has users assigned to it.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * All permissions grouped by their module prefix (e.g. "product.view" -> "product"),
     * in the fixed module display order defined in self::MODULES.
     *
     * @return array<string, array{label: string, permissions: Collection}>
     */
    private function groupedPermissions(): array
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(
            fn (Permission $permission) => explode('.', $permission->name)[0]
        );

        $grouped = [];

        foreach (self::MODULES as $prefix => $label) {
            if ($permissions->has($prefix)) {
                $grouped[$prefix] = [
                    'label' => $label,
                    'permissions' => $permissions->get($prefix),
                ];
            }
        }

        // Any permission whose prefix is not listed in self::MODULES still has to
        // be rendered, otherwise saving a role would silently strip it (the form
        // posts the full desired set and we syncPermissions() against it).
        foreach ($permissions as $prefix => $modulePermissions) {
            if (! isset($grouped[$prefix])) {
                $grouped[$prefix] = [
                    'label' => Str::headline($prefix),
                    'permissions' => $modulePermissions,
                ];
            }
        }

        return $grouped;
    }
}
