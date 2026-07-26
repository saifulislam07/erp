@extends('layouts.admin')

@section('content_title', 'Add Store')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Store</h3>
        </div>

        <form action="{{ route('admin.stores.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.stores.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
