@extends('layouts.admin')

@section('content_title', 'Expense Report')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.expenses.report') }}" method="get">
            <div class="card-body row">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Expense Head</label>
                    <select name="expense_head_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($expenseHeads as $head)
                            <option value="{{ $head->id }}" {{ (int) request('expense_head_id') === $head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('admin.expenses.report.excel', request()->query()) }}" class="btn btn-success mr-2">Excel</a>
                    <a href="{{ route('admin.expenses.report.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Breakdown by Head</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                @foreach ($byHead as $headName => $total)
                    <tr><th>{{ $headName }}</th><td>{{ number_format($total, 2) }}</td></tr>
                @endforeach
                <tr><th>Grand Total</th><td>{{ number_format($grandTotal, 2) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Expenses</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr><th>Expense ID</th><th>Head</th><th>Amount</th><th>Date</th><th>Method</th></tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_id }}</td>
                            <td>{{ $expense->expenseHead->name }}</td>
                            <td>{{ $expense->amount }}</td>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($expense->payment_method) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
