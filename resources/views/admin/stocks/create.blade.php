@extends('layouts.admin')

@section('content_title', 'Add Stock')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Stock (Manual Adjustment)</h3>
        </div>

        <form action="{{ route('admin.stocks.store') }}" method="post">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="product_id">Product</label>
                    <select name="product_id" id="product_id" class="form-control @error('product_id') is-invalid @enderror">
                        <option value="">-- Select Product --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ (int) old('product_id') === $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->unique_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="store_id">Store</label>
                    <select name="store_id" id="store_id" class="form-control @error('store_id') is-invalid @enderror">
                        <option value="">-- Select Store --</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ (int) old('store_id') === $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" step="0.01" name="quantity" id="quantity"
                                class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}">
                            @error('quantity')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="purchase_price">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="purchase_price"
                                class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price') }}">
                            @error('purchase_price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date"
                                class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}">
                            @error('expiry_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="batch_number">Batch Number</label>
                    <input type="text" name="batch_number" id="batch_number"
                        class="form-control @error('batch_number') is-invalid @enderror" value="{{ old('batch_number') }}">
                    @error('batch_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.stocks.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
