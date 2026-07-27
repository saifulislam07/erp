@extends('layouts.admin')

@section('content_title', 'Stock')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.stocks.index') }}" method="get">
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
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stock List</h3>
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
            <table id="stocks-table" class="table table-bordered table-striped">
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
                <tbody>
                    @foreach ($stocks as $stock)
                        @php
                            $rowClass = '';
                            $statusLabel = 'OK';
                            if ($stock->expiry_date) {
                                if ($stock->expiry_date->isPast()) {
                                    $rowClass = 'table-danger';
                                    $statusLabel = 'Expired';
                                } elseif ($stock->expiry_date->diffInDays(now()) <= 30) {
                                    $rowClass = 'table-warning';
                                    $statusLabel = 'Expires < 1 month';
                                } elseif ($stock->expiry_date->diffInDays(now()) <= 90) {
                                    $rowClass = '';
                                    $statusLabel = 'Expires < 3 months';
                                }
                            }
                        @endphp
                        <tr class="{{ $rowClass }}" style="{{ $statusLabel === 'Expires < 3 months' ? 'background-color:#fff3cd;' : '' }}">
                            <td>{{ $stock->product->name }} ({{ $stock->product->unique_id }})</td>
                            <td>{{ $stock->product->category?->name }}</td>
                            <td>{{ $stock->store->name }}</td>
                            <td>{{ $stock->batch_number ?? '-' }}</td>
                            <td>{{ $stock->quantity }}</td>
                            <td>{{ $stock->product->unit?->name }}</td>
                            <td>{{ $stock->expiry_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $statusLabel }}</td>
                            <td>
                                <a href="{{ route('admin.stocks.edit', $stock) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.stocks.destroy', $stock) }}" method="post" class="d-inline" data-confirm="Delete this stock entry?" data-confirm-text="This stock entry will be removed." data-confirm-button="Yes, remove it">
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
@endsection

@push('js')
    <script>
        $(function () {
            $('#stocks-table').DataTable();

        });
    </script>
@endpush
