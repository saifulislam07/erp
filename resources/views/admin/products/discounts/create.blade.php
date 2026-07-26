@extends('layouts.admin')

@section('content_title', 'Add Discount')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Discount for {{ $product->name }}</h3>
        </div>

        <form action="{{ route('admin.products.discounts.store', $product) }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.products.discounts.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.products.discounts.index', $product) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
