@extends('layouts.admin')

@section('content_title', $product->name)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $product->name }} ({{ $product->unique_id }})</h3>
            <div class="card-tools">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    @if ($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" class="img-fluid" alt="{{ $product->name }}">
                    @else
                        <p class="text-muted">No image</p>
                    @endif
                </div>
                <div class="col-md-9">
                    <table class="table table-sm">
                        <tr><th>Category</th><td>{{ $product->category?->name }}</td></tr>
                        <tr><th>Sub-Category</th><td>{{ $product->subCategory?->name ?? '-' }}</td></tr>
                        <tr><th>Unit</th><td>{{ $product->unit?->name }} ({{ $product->unit?->symbol }})</td></tr>
                        <tr><th>MRP Price</th><td>{{ $product->mrp_price }}</td></tr>
                        <tr><th>Purchase Price</th><td>{{ $product->purchase_price }}</td></tr>
                        <tr><th>Sale Price</th><td>{{ $product->sale_price }}</td></tr>
                        <tr><th>VAT</th><td>{{ $product->vat_percentage > 0 ? $product->vat_percentage.'%' : 'No VAT' }}</td></tr>
                        <tr><th>Current Stock</th><td>{{ $stock }}</td></tr>
                        <tr><th>Status</th><td>{{ $product->status ? 'Active' : 'Inactive' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header p-2">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#stock" data-toggle="tab">Stock</a></li>
                <li class="nav-item"><a class="nav-link" href="#purchase" data-toggle="tab">Purchase History</a></li>
                <li class="nav-item"><a class="nav-link" href="#sales" data-toggle="tab">Sales History</a></li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane active" id="stock">
                    <p>Current available stock: <strong>{{ $stock }}</strong></p>
                    <p class="text-muted">Detailed stock breakdown by store will be available once the Stock module is in use.</p>
                </div>
                <div class="tab-pane" id="purchase">
                    @if ($purchaseHistory->isEmpty())
                        <p class="text-muted mb-0">No purchase history yet.</p>
                    @else
                        <table class="table table-sm">
                            @foreach ($purchaseHistory as $item)
                                <tr>
                                    <td>{{ $item->created_at ?? '' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->purchase_price }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
                <div class="tab-pane" id="sales">
                    @if ($salesHistory->isEmpty())
                        <p class="text-muted mb-0">No sales history yet.</p>
                    @else
                        <table class="table table-sm">
                            @foreach ($salesHistory as $item)
                                <tr>
                                    <td>{{ $item->created_at ?? '' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->unit_price }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
