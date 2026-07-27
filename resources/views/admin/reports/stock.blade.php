@extends('layouts.admin')

@section('content_title', 'Stock Report')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.reports.stock') }}" method="get">
            <div class="card-body row">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (int) request('category_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Store</label>
                    <select name="store_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ (int) request('store_id') === $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex align-items-end justify-content-between mt-2">
                    @include('admin.partials.date-presets')
                    <div>
                        <button type="submit" class="btn btn-primary mr-2">Filter</button>
                        <a href="{{ route('admin.reports.stock.excel', request()->query()) }}" class="btn btn-success mr-2">Excel</a>
                        <a href="{{ route('admin.reports.stock.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stock</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Store</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Purchase Price</th>
                        <th>Value</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stocks as $stock)
                        <tr>
                            <td>{{ $stock->product->name }}</td>
                            <td>{{ $stock->product->category?->name }}</td>
                            <td>{{ $stock->store->name }}</td>
                            <td>{{ $stock->batch_number }}</td>
                            <td>{{ $stock->quantity }}</td>
                            <td>{{ money($stock->purchase_price) }}</td>
                            <td>{{ money($stock->quantity * $stock->purchase_price) }}</td>
                            <td>{{ $stock->expiry_date?->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Total Value</th>
                        <th colspan="2">{{ money($totalValue) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
