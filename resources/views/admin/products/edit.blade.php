@extends('layouts.admin')

@section('content_title', 'Edit Product')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Product</h3>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.products.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
