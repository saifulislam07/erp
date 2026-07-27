@extends('layouts.admin')

@section('content_title', 'Employees')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Employees</h3>
            <div class="card-tools">
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Employee
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="employees-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->employee_id }}</td>
                            <td>{{ $employee->department->name ?? '-' }}</td>
                            <td>{{ $employee->roles->pluck('name')->join(', ') ?: '-' }}</td>
                            <td>
                                <form action="{{ route('admin.employees.toggle-status', $employee) }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $employee->status ? 'btn-success' : 'btn-secondary' }}">
                                        {{ $employee->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-info reset-password-btn"
                                    data-url="{{ route('admin.employees.reset-password', $employee) }}">
                                    <i class="fas fa-key"></i>
                                </button>
                                <form action="{{ route('admin.employees.destroy', $employee) }}" method="post"
                                    class="d-inline" data-confirm="Delete this employee?" data-confirm-text="This employee will be soft deleted." data-confirm-button="Delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
            $('#employees-table').DataTable();

            $('.reset-password-btn').on('click', function () {
                $('#reset-password-form').attr('action', $(this).data('url'));
                $('#reset-password-modal').modal('show');
            });
        });
    </script>
@endpush
