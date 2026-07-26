@extends('layouts.admin')

@section('content_title', 'Edit Unit')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Unit</h3>
        </div>

        <form action="{{ route('admin.units.update', $unit) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.units.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.units.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
