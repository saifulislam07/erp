@extends('layouts.admin')

@section('content_title', 'Departments')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="departments-count">All Departments</h3>
            <div class="card-tools">
                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Department
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="departments-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
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
            ERP.serverTable('#departments-table', {
                url: '{{ route('admin.departments.index') }}',
                count: '#departments-count',
                noun: 'department',
                empty: 'No departments yet.',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'users_count', name: 'users_count', orderable: false, searchable: false },
                    { data: 'created_on', name: 'created_on', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
