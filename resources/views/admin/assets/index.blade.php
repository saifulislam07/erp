@extends('layouts.admin')

@section('content_title', 'Assets')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="assets-count">All Assets</h3>
            <div class="card-tools">
                <a href="{{ route('admin.assets.report') }}" class="btn btn-secondary btn-sm">Report</a>
                <a href="{{ route('admin.assets.create') }}" class="btn btn-primary btn-sm">Add Asset</a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.assets.index') }}" method="get" class="form-inline mb-3"
                  id="assets-filter" data-no-submit-guard>
                <input type="text" name="q" class="form-control mr-2" placeholder="Search ID/name/serial..." value="{{ request('q') }}">
                <select name="status" class="form-control mr-2">
                    <option value="">-- All Statuses --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="disposed" {{ request('status') === 'disposed' ? 'selected' : '' }}>Disposed</option>
                    <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="#" class="btn btn-secondary ml-1" data-table-clear>Clear</a>
            </form>

            <table id="assets-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Asset ID</th>
                        <th>Name</th>
                        <th>Serial Number</th>
                        <th>Category</th>
                        <th>Purchase Price</th>
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
            ERP.serverTable('#assets-table', {
                url: '{{ route('admin.assets.index') }}',
                filter: '#assets-filter',
                count: '#assets-count',
                noun: 'asset',
                empty: 'No assets recorded yet.',
                order: [[0, 'desc']],
                columns: [
                    { data: 'asset_id', name: 'asset_id' },
                    { data: 'name', name: 'name' },
                    { data: 'serial_number', name: 'serial_number' },
                    { data: 'category', name: 'category' },
                    { data: 'purchase_price', name: 'purchase_price' },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
