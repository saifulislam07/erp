@extends('layouts.admin')

@section('content_title', 'Roles')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="roles-count">All Roles</h3>
            @can('role.create')
                <div class="card-tools">
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Role
                    </a>
                </div>
            @endcan
        </div>

        <div class="card-body">
            <table id="roles-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th>Permissions Count</th>
                        <th>Users</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#roles-table', {
                url: '{{ route('admin.roles.index') }}',
                count: '#roles-count',
                noun: 'role',
                empty: 'No roles defined.',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'permissions_count', name: 'permissions_count', orderable: false, searchable: false },
                    { data: 'users_count', name: 'users_count', orderable: false, searchable: false },
                    { data: 'created_on', name: 'created_on', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
