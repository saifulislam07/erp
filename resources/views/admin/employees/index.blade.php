@extends('layouts.admin')

@section('content_title', 'Employees')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="employees-count">All Employees</h3>
            <div class="card-tools">
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Employee
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="employees-table" class="table table-bordered table-striped" style="width: 100%">
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
            ERP.serverTable('#employees-table', {
                url: '{{ route('admin.employees.index') }}',
                count: '#employees-count',
                noun: 'employee',
                empty: 'No employees yet.',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'employee_id', name: 'employee_id' },
                    { data: 'department_name', name: 'department_name' },
                    { data: 'role_names', name: 'role_names', orderable: false },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });

            // Delegated: the buttons are redrawn by DataTables on every page change.
            $(document).on('click', '.reset-password-btn', function () {
                $('#reset-password-form').attr('action', $(this).data('url'));
                $('#reset-password-modal').modal('show');
            });
        });
    </script>
@endpush
