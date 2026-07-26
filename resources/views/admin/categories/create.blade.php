@extends('layouts.admin')

@section('content_title', 'Add Category')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Category</h3>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.categories.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
