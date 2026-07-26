@extends('layouts.admin')

@section('content_title', 'Edit Supplier')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Supplier</h3>
        </div>

        <form action="{{ route('admin.suppliers.update', $supplier) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.suppliers.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
