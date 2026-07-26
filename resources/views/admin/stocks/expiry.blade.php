@extends('layouts.admin')

@section('content_title', 'Expiring Stock - ' . $label)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stock Expiring Within {{ $label }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.stocks.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <div class="card-body">
            @if ($stocks->isEmpty())
                <p class="text-muted mb-0">No stock entries expiring in this window.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Store</th>
                            <th>Batch</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stocks as $stock)
                            <tr class="{{ $stock->expiry_date->isPast() ? 'table-danger' : 'table-warning' }}">
                                <td>{{ $stock->product->name }} ({{ $stock->product->unique_id }})</td>
                                <td>{{ $stock->product->category?->name }}</td>
                                <td>{{ $stock->store->name }}</td>
                                <td>{{ $stock->batch_number ?? '-' }}</td>
                                <td>{{ $stock->quantity }}</td>
                                <td>{{ $stock->expiry_date->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
