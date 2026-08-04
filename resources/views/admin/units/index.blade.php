@extends('layouts.admin')

@section('content_title', 'Units')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="units-count">All Units</h3>
            <div class="card-tools">
                <a href="{{ route('admin.units.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Unit
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="units-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Products</th>
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
            ERP.serverTable('#units-table', {
                url: '{{ route('admin.units.index') }}',
                count: '#units-count',
                noun: 'unit',
                empty: 'No units yet.',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
