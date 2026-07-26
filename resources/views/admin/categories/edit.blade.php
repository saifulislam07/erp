@extends('layouts.admin')

@section('content_title', 'Edit Category')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Category</h3>
        </div>

        <form action="{{ route('admin.categories.update', $category) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.categories.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
