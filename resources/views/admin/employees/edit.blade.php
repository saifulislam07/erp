@extends('layouts.admin')

@section('content_title', 'Edit Employee')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Employee</h3>
        </div>

        <form action="{{ route('admin.employees.update', $employee) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.employees.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
