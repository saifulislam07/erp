<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmployeePasswordResetRequest;
use App\Http\Requests\Admin\EmployeeRequest;
use App\Models\Department;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = User::with(['department', 'roles'])
            ->where('is_admin', false)
            ->latest()
            ->get();

        return view('admin.employees.index', compact('employees'));
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
