@extends('layouts.admin')

@section('content_title', 'Edit Expense')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Expense</h3>
        </div>
        <form action="{{ route('admin.expenses.update', $expense) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.expenses.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
