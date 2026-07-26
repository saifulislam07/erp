@extends('layouts.admin')

@section('content_title', 'Expenses')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.expenses.index') }}" method="get">
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
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Expenses</h3>
            <div class="card-tools">
                <a href="{{ route('admin.expense-heads.index') }}" class="btn btn-secondary btn-sm">Expense Heads</a>
                <a href="{{ route('admin.expenses.report') }}" class="btn btn-secondary btn-sm">Report</a>
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">Add Expense</a>
            </div>
        </div>
        <div class="card-body">
            <table id="expenses-table" class="table table-bordered table-striped">
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
                <tbody>
                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_id }}</td>
                            <td>{{ $expense->expenseHead->name }}</td>
                            <td>{{ $expense->amount }}</td>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($expense->payment_method) }}</td>
                            <td>
                                <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="post" class="d-inline delete-form">
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

@push('js')
    <script>
        $(function () {
            $('#expenses-table').DataTable();

            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will reverse the cash/bank debit for this expense.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush
