@extends('layouts.admin')

@section('content_title', 'Add Expense Head')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Expense Head</h3>
        </div>
        <form action="{{ route('admin.expense-heads.store') }}" method="post">
            @csrf
            <div class="card-body">
                @include('admin.expense-heads.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.expense-heads.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
