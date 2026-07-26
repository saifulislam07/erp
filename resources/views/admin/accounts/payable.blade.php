@extends('layouts.admin')

@section('content_title', 'Accounts Payable')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.accounts.payable') }}" method="get">
            <div class="card-body row">
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="settled" class="form-control">
                        <option value="">-- All --</option>
                        <option value="0" {{ request('settled') === '0' ? 'selected' : '' }}>Unsettled</option>
                        <option value="1" {{ request('settled') === '1' ? 'selected' : '' }}>Settled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Supplier</label>
                    <select name="party_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (int) request('party_id') === $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payables (money we owe suppliers)</h3>
            <div class="card-tools">
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.accounts.create', ['type' => 'payable']) }}" class="btn btn-primary btn-sm">Add Manual Entry</a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accounts as $account)
                        <tr>
                            <td>{{ $account->party?->name }}</td>
                            <td>{{ $account->amount }}</td>
                            <td>{{ $account->due_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $account->description }}</td>
                            <td>
                                <span class="badge badge-{{ $account->is_settled ? 'success' : 'warning' }}">
                                    {{ $account->is_settled ? 'Settled' : 'Unsettled' }}
                                </span>
                            </td>
                            <td>
                                @if (!$account->is_settled && auth()->user()->is_admin)
                                    <form action="{{ route('admin.accounts.settle', $account) }}" method="post" class="form-inline d-inline">
                                        @csrf
                                        <select name="method" class="form-control form-control-sm mr-1" required>
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank</option>
                                            <option value="mobile_banking">Mobile Banking</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success">Settle</button>
                                    </form>
                                @endif
                                @if (auth()->user()->is_admin && !$account->reference_type)
                                    <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('admin.accounts.destroy', $account) }}" method="post" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
