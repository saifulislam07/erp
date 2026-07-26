@extends('layouts.admin')

@section('content_title', 'Add Department')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Department</h3>
        </div>

        <form action="{{ route('admin.departments.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.departments.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
