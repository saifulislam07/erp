@extends('layouts.admin')

@section('content_title', 'Edit Account Entry')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Account Entry</h3>
        </div>

        <form action="{{ route('admin.accounts.update', $account) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.accounts.form')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.accounts.'.$account->type) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
