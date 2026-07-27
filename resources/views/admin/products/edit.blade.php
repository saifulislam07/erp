@extends('layouts.admin')

@section('content_title', 'Edit '.$product->name)

@section('content_body')
    <form action="{{ route('admin.products.update', $product) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.products.form')

        <div class="card">
            <div class="card-body page-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Save changes
                </button>
                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary">Cancel</a>

                <a href="{{ route('admin.products.discounts.index', $product) }}" class="btn btn-outline-primary ml-auto">
                    <i class="fas fa-tags mr-1"></i> Manage discounts
                </a>
            </div>
        </div>
    </form>
@endsection
