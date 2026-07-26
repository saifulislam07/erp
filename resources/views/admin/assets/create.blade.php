@extends('layouts.admin')

@section('content_title', 'Add Asset')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Asset</h3>
        </div>
        <form action="{{ route('admin.assets.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('admin.assets.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
