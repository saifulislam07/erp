@extends('layouts.admin')

@section('content_title', 'Adjust Stock')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Adjust Stock: {{ $stock->product->name }} @ {{ $stock->store->name }}</h3>
        </div>

        <form action="{{ route('admin.stocks.update', $stock) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                <p>Current quantity: <strong>{{ $stock->quantity }}</strong></p>

                <div class="form-group">
                    <label for="quantity">New Quantity</label>
                    <input type="number" step="0.01" name="quantity" id="quantity"
                        class="form-control @error('quantity') is-invalid @enderror"
                        value="{{ old('quantity', $stock->quantity) }}">
                    @error('quantity')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reason">Reason for Adjustment</label>
                    <textarea name="reason" id="reason" rows="3"
                        class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                    @error('reason')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.stocks.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
