@extends('layouts.admin')

@section('content_title', 'Suppliers')

@section('content_body')
    <form method="get" class="filter-bar" id="suppliers-filter" data-no-submit-guard>
        <div class="row align-items-end">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="q">Search</label>
                    <input type="text" name="q" id="q" class="form-control"
                        value="{{ request('q') }}" placeholder="Name, supplier ID, phone or company">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group page-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="#" class="btn btn-secondary" data-table-clear>Clear</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="suppliers-count">All Suppliers</h3>
            <div class="card-tools">
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Supplier
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="suppliers-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Company</th>
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
            ERP.serverTable('#suppliers-table', {
                url: '{{ route('admin.suppliers.index') }}',
                filter: '#suppliers-filter',
                count: '#suppliers-count',
                noun: 'supplier',
                empty: 'No suppliers yet.',
                order: [[0, 'desc']],
                columns: [
                    { data: 'unique_id', name: 'unique_id' },
                    { data: 'name', name: 'name' },
                    { data: 'phone', name: 'phone' },
                    { data: 'company_name', name: 'company_name' },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
