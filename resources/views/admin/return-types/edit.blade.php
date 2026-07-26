@extends('layouts.admin')

@section('content_title', 'Edit Return Type')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Return Type</h3>
        </div>
        <form action="{{ route('admin.return-types.update', $returnType) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.return-types.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.return-types.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
