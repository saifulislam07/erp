@extends('layouts.admin')

@section('content_title', 'Add Employee')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Employee</h3>
        </div>

        <form action="{{ route('admin.employees.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.employees.form')

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
