@extends('layouts.admin')

@section('content_title', 'Edit Role')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Role: {{ $role->name }}</h3>
        </div>

        <form action="{{ route('admin.roles.update', $role) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.roles.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
