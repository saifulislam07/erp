@extends('layouts.admin')

@section('content_title', 'Expenses')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.expenses.index') }}" method="get" id="expenses-filter" data-no-submit-guard>
            <div class="card-body row">
                <div class="col-md-3">
                    <label>Expense Head</label>
                    <select name="expense_head_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($expenseHeads as $head)
                            <option value="{{ $head->id }}" {{ (int) request('expense_head_id') === $head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Method</label>
                    <select name="method" class="form-control">
                        <option value="">-- All --</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>Bank</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="#" class="btn btn-secondary ml-1" data-table-clear>Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="expenses-count">All Expenses</h3>
            <div class="card-tools">
                <a href="{{ route('admin.expense-heads.index') }}" class="btn btn-secondary btn-sm">Expense Heads</a>
                <a href="{{ route('admin.expenses.report') }}" class="btn btn-secondary btn-sm">Report</a>
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">Add Expense</a>
            </div>
        </div>
        <div class="card-body">
            <table id="expenses-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Expense ID</th>
                        <th>Head</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#expenses-table', {
                url: '{{ route('admin.expenses.index') }}',
                filter: '#expenses-filter',
                count: '#expenses-count',
                noun: 'expense',
                empty: 'No expenses recorded yet.',
                order: [[3, 'desc']],
                columns: [
                    { data: 'expense_id', name: 'expense_id' },
                    { data: 'head_name', name: 'head_name' },
                    { data: 'amount', name: 'amount' },
                    { data: 'expense_date', name: 'expense_date' },
                    { data: 'payment_method', name: 'payment_method' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
