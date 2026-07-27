@extends('layouts.admin')

@section('content_title', 'Employee Detail')

@section('content_body')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $employee->name }}</h3>
                    <div class="card-tools">
                        <span class="badge {{ $employee->status ? 'badge-success' : 'badge-secondary' }}">
                            {{ $employee->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr><th style="width: 40%">Employee ID</th><td>{{ $employee->employee_id ?? '-' }}</td></tr>
                        <tr><th>Email</th><td>{{ $employee->email }}</td></tr>
                        <tr><th>Phone</th><td>{{ $employee->phone ?? '-' }}</td></tr>
                        <tr><th>Address</th><td>{{ $employee->address ?? '-' }}</td></tr>
                        <tr><th>Department</th><td>{{ $employee->department->name ?? '-' }}</td></tr>
                        <tr>
                            <th>Role</th>
                            <td>
                                @forelse ($employee->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @empty
                                    -
                                @endforelse
                            </td>
                        </tr>
                        <tr><th>Joined</th><td>{{ $employee->created_at->format('Y-m-d') }}</td></tr>
                    </table>
                </div>

                <div class="card-footer">
                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button type="button" class="btn btn-info btn-sm" id="reset-password-btn"
                        data-url="{{ route('admin.employees.reset-password', $employee) }}">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Menu Access (Permissions)</h3>
                </div>
                <div class="card-body">
                    @php
                        $permissions = $employee->roles->flatMap->permissions->pluck('name')->unique()->sort();
                        $groupedPermissions = $permissions->groupBy(fn ($name) => explode('.', $name)[0]);
                    @endphp

                    @if ($employee->is_admin)
                        <p class="text-success mb-0">
                            <i class="fas fa-shield-alt"></i> Super Admin — full access to every module.
                        </p>
                    @elseif ($groupedPermissions->isEmpty())
                        <p class="text-muted mb-0">No permissions assigned to this employee's role.</p>
                    @else
                        @foreach ($groupedPermissions as $module => $modulePermissions)
                            <div class="mb-2">
                                <strong class="text-capitalize">{{ $module }}</strong><br>
                                @foreach ($modulePermissions as $permission)
                                    <span class="badge badge-light border">
                                        {{ \Illuminate\Support\Str::after($permission, '.') }}
                                    </span>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Salaries</h3>
                </div>
                <div class="card-body p-0">
                    @if ($salaries->isEmpty())
                        <p class="text-muted p-3 mb-0">No salary records yet.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Basic</th>
                                    <th>Deduction</th>
                                    <th>Net</th>
                                    <th>Method</th>
                                    <th>Paid At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($salaries as $salary)
                                    <tr>
                                        <td>{{ $salary->month }}</td>
                                        <td>{{ $salary->basic_salary }}</td>
                                        <td>{{ $salary->deduction }}</td>
                                        <td>{{ $salary->net_salary }}</td>
                                        <td>{{ ucfirst($salary->payment_method) }}</td>
                                        <td>{{ $salary->paid_at?->format('Y-m-d') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reset-password-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="reset-password-form" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Employee Password</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('#reset-password-btn').on('click', function () {
                $('#reset-password-form').attr('action', $(this).data('url'));
                $('#reset-password-modal').modal('show');
            });
        });
    </script>
@endpush
