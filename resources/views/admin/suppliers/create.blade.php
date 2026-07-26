@extends('layouts.admin')

@section('content_title', 'Add Supplier')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Supplier</h3>
        </div>

        <form action="{{ route('admin.suppliers.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.suppliers.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
