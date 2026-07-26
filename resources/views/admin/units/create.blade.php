@extends('layouts.admin')

@section('content_title', 'Add Unit')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Unit</h3>
        </div>

        <form action="{{ route('admin.units.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.units.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.units.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
