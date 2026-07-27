@extends('layouts.admin')

@section('content_title', 'Low Stock Products')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Products Below Minimum Stock Threshold</h3>
            <div class="card-tools">
                <a href="{{ route('admin.stocks.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <div class="card-body">
            @if ($products->isEmpty())
                <p class="text-muted mb-0">No products are currently below their minimum stock threshold.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Total Stock</th>
                            <th>Threshold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="table-warning">
                                <td>{{ $product->name }} ({{ $product->unique_id }})</td>
                                <td>{{ $product->category?->name }}</td>
                                <td>{{ $product->unit?->name }}</td>
                                <td>{{ $product->total_stock }}</td>
                                <td>{{ $product->min_stock_threshold }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
