@extends('layouts.admin')

@section('content_title', 'Edit Asset')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Asset</h3>
        </div>
        <form action="{{ route('admin.assets.update', $asset) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.assets.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
