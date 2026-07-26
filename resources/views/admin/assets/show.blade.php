@extends('layouts.admin')

@section('content_title', 'Asset ' . $asset->asset_id)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $asset->name }} ({{ $asset->asset_id }})</h3>
            <div class="card-tools">
                <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-warning btn-sm">Edit</a>
                <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Serial Number</th><td>{{ $asset->serial_number ?? '-' }}</td></tr>
                <tr><th>Category</th><td>{{ $asset->category ?? '-' }}</td></tr>
                <tr><th>Quantity</th><td>{{ $asset->quantity }}</td></tr>
                <tr><th>Purchase Price</th><td>{{ $asset->purchase_price }}</td></tr>
                <tr><th>Purchase Date</th><td>{{ $asset->purchase_date->format('Y-m-d') }}</td></tr>
                <tr><th>Warranty Until</th><td>{{ $asset->warranty_until?->format('Y-m-d') ?? '-' }}</td></tr>
                <tr><th>Expire Date</th><td>{{ $asset->expire_date?->format('Y-m-d') ?? '-' }}</td></tr>
                <tr><th>Place of Purchase</th><td>{{ $asset->place_of_purchase ?? '-' }}</td></tr>
                <tr><th>Supplier</th><td>{{ $asset->supplier_name ?? '-' }}</td></tr>
                <tr><th>Supplier Address</th><td>{{ $asset->supplier_address ?? '-' }}</td></tr>
                <tr><th>Description</th><td>{{ $asset->description ?? '-' }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($asset->status) }}</td></tr>
                <tr><th>Created By</th><td>{{ $asset->creator?->name }}</td></tr>
                <tr>
                    <th>Invoice</th>
                    <td>
                        @if ($asset->invoice_file)
                            <a href="{{ asset('storage/'.$asset->invoice_file) }}" target="_blank">View Invoice</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection
