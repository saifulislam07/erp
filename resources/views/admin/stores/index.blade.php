@extends('layouts.admin')

@section('content_title', 'Stores')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="stores-count">All Stores</h3>
            <div class="card-tools">
                <a href="{{ route('admin.stores.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Store
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="stores-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Stock Entries</th>
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
            ERP.serverTable('#stores-table', {
                url: '{{ route('admin.stores.index') }}',
                count: '#stores-count',
                noun: 'store',
                empty: 'No stores yet.',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'location', name: 'location' },
                    { data: 'stocks_count', name: 'stocks_count', orderable: false, searchable: false },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
