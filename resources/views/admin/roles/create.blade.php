@extends('layouts.admin')

@section('content_title', 'Add Role')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Role</h3>
        </div>

        <form action="{{ route('admin.roles.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.roles.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
