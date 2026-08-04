@extends('layouts.admin')

@section('content_title', 'Stock')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.stocks.index') }}" method="get" id="stocks-filter" data-no-submit-guard>
            <div class="card-body row">
                <div class="col-md-3">
                    <label>Product Name/ID</label>
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label>Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (int) request('category_id') === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Store</label>
                    <select name="store_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ (int) request('store_id') === $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="#" class="btn btn-secondary ml-1" data-table-clear>Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="stocks-count">Stock List</h3>
            <div class="card-tools">
                <a href="{{ route('admin.stocks.low-quantity') }}" class="btn btn-warning btn-sm">Low Quantity</a>
                <a href="{{ route('admin.stocks.expiry.one-month') }}" class="btn btn-danger btn-sm">Expiring (1mo)</a>
                <a href="{{ route('admin.stocks.expiry.three-month') }}" class="btn btn-secondary btn-sm">Expiring (3mo)</a>
                <a href="{{ route('admin.stocks.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Stock
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="stocks-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Store</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Expiry Date</th>
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
            ERP.serverTable('#stocks-table', {
                url: '{{ route('admin.stocks.index') }}',
                filter: '#stocks-filter',
                count: '#stocks-count',
                noun: 'stock entry',
                empty: 'No stock on hand.',
                order: [[6, 'asc']],
                columns: [
                    { data: 'product_label', name: 'product_label', orderable: false },
                    { data: 'category_name', name: 'category_name', orderable: false },
                    { data: 'store_name', name: 'store_name' },
                    { data: 'batch_number', name: 'batch_number' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'unit_name', name: 'unit_name', orderable: false },
                    { data: 'expires_on', name: 'expires_on' },
                    { data: 'expiry_label', name: 'expiry_label' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
