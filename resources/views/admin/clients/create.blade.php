@extends('layouts.admin')

@section('content_title', 'Add Client')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Client / Agent</h3>
        </div>

        <form action="{{ route('admin.clients.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.clients.form')

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
                <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
