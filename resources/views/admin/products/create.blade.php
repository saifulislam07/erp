@extends('layouts.admin')

@section('content_title', 'Add product')

@section('content_body')
    <form action="{{ route('admin.products.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        @include('admin.products.form')

        <div class="card">
            <div class="card-body page-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Save product
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
@endsection
