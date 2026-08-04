@extends('layouts.admin')

@section('content_title', 'Categories')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="categories-count">All Categories</h3>
            <div class="card-tools">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Category
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="categories-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Products</th>
                        <th>Status</th>
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
            ERP.serverTable('#categories-table', {
                url: '{{ route('admin.categories.index') }}',
                count: '#categories-count',
                noun: 'category',
                nounPlural: 'categories',
                empty: 'No categories yet.',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'parent_name', name: 'parent_name' },
                    { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
