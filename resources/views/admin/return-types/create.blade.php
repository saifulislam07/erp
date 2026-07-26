@extends('layouts.admin')

@section('content_title', 'Add Return Type')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Return Type</h3>
        </div>
        <form action="{{ route('admin.return-types.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.return-types.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.return-types.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
