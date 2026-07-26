@extends('layouts.admin')

@section('content_title', 'Edit Client')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Client / Agent</h3>
        </div>

        <form action="{{ route('admin.clients.update', $client) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.clients.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
