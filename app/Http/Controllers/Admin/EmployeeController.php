<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmployeePasswordResetRequest;
use App\Http\Requests\Admin\EmployeeRequest;
use App\Models\Department;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData();
        }

        return view('admin.employees.index');
    }

    /**
     * Server-side DataTables feed for the employee listing.
     */
    protected function indexData(): JsonResponse
    {
        $query = User::query()
            ->with(['department', 'roles'])
            ->where('is_admin', false)
            ->select('users.*');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('department_name', fn (User $employee) => e($employee->department?->name ?? '-'))
            ->addColumn('role_names', fn (User $employee) => e($employee->roles->pluck('name')->join(', ') ?: '-'))
            ->addColumn('state', fn (User $employee) => view('admin.employees.partials.status-cell', compact('employee'))->render())
            ->addColumn('actions', fn (User $employee) => view('admin.employees.partials.actions', compact('employee'))->render())
            ->filterColumn('department_name', fn ($query, $keyword) => $query->whereHas('department', fn ($d) => $d->where('name', 'like', "%{$keyword}%")))
            ->filterColumn('role_names', fn ($query, $keyword) => $query->whereHas('roles', fn ($r) => $r->where('name', 'like', "%{$keyword}%")))
            ->orderColumn('department_name', 'department_id $1')
            ->orderColumn('state', 'status $1')
            ->rawColumns(['state', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        $roles = $this->assignableRoles();

        return view('admin.employees.create', compact('departments', 'roles'));
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'department_id' => $request->department_id,
            'password' => $request->password,
            'is_admin' => false,
            'status' => true,
        ]);

        $employee->assignRole($request->role);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(User $employee): View
    {
        $employee->load(['department', 'roles.permissions']);

        $salaries = Salary::where('user_id', $employee->id)
            ->latest('month')
            ->take(12)
            ->get();

        return view('admin.employees.show', compact('employee', 'salaries'));
    }

    public function edit(User $employee): View
    {
        $departments = Department::orderBy('name')->get();
        $roles = $this->assignableRoles();

        return view('admin.employees.edit', compact('employee', 'departments', 'roles'));
    }

    public function update(EmployeeRequest $request, User $employee): RedirectResponse
    {
        $employee->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'department_id' => $request->department_id,
        ]);

        $employee->syncRoles([$request->role]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function toggleStatus(User $employee): RedirectResponse
    {
        $employee->update(['status' => ! $employee->status]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee status updated.');
    }

    public function resetPassword(EmployeePasswordResetRequest $request, User $employee): RedirectResponse
    {
        $employee->update(['password' => $request->validated('new_password')]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee password reset successfully.');
    }

    /**
     * Role names an employee can be assigned. "Admin" is excluded because admins
     * are not created from this UI (employees are always is_admin = false).
     *
     * @return Collection<int, string>
     */
    private function assignableRoles(): Collection
    {
        return Role::where('guard_name', 'web')
            ->where('name', '!=', 'Admin')
            ->orderBy('name')
            ->pluck('name');
    }
}
