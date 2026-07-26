@extends('layouts.admin')

@section('content_title', 'Asset Report')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.assets.report') }}" method="get">
            <div class="card-body row">
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="disposed" {{ request('status') === 'disposed' ? 'selected' : '' }}>Disposed</option>
                        <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>From Purchase Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label>To Purchase Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('admin.assets.report.excel', request()->query()) }}" class="btn btn-success mr-2">Excel</a>
                    <a href="{{ route('admin.assets.report.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Asset ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Purchase Price</th>
                        <th>Purchase Date</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr>
                            <td>{{ $asset->asset_id }}</td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->category }}</td>
                            <td>{{ $asset->purchase_price }}</td>
                            <td>{{ $asset->purchase_date->format('Y-m-d') }}</td>
                            <td>{{ $asset->quantity }}</td>
                            <td>{{ ucfirst($asset->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Total Value</th>
                        <th>{{ number_format($assets->sum('purchase_price'), 2) }}</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
