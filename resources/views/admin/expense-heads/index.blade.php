@extends('layouts.admin')

@section('content_title', 'Expense Heads')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Expense Heads</h3>
            <div class="card-tools">
                <a href="{{ route('admin.expense-heads.create') }}" class="btn btn-primary btn-sm">Add Expense Head</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Expenses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenseHeads as $head)
                        <tr>
                            <td>{{ $head->name }}</td>
                            <td>{{ $head->description }}</td>
                            <td>{{ $head->expenses_count }}</td>
                            <td>
                                <a href="{{ route('admin.expense-heads.edit', $head) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.expense-heads.destroy', $head) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
