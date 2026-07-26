@extends('layouts.admin')

@section('content_title', 'Purchases')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.purchases.index') }}" method="get">
            <div class="card-body row">
                <div class="col-md-2">
                    <label>Purchase ID</label>
                    <input type="text" name="purchase_id" class="form-control" value="{{ request('purchase_id') }}">
                </div>
                <div class="col-md-2">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (int) request('supplier_id') === $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
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
                <div class="col-md-3">
                    <label>Period</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="period" value="weekly" class="form-check-input" {{ request('period') === 'weekly' ? 'checked' : '' }}>
                            <label class="form-check-label">Weekly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="period" value="monthly" class="form-check-input" {{ request('period') === 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label">Monthly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="period" value="yearly" class="form-check-input" {{ request('period') === 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label">Yearly</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Purchases</h3>
            <div class="card-tools">
                <a href="{{ route('admin.purchases.report') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-chart-bar"></i> Report
                </a>
                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Purchase
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="purchases-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_id }}</td>
                            <td>{{ $purchase->supplier?->name }}</td>
                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                            <td>{{ $purchase->total_amount }}</td>
                            <td>{{ $purchase->paid_amount }}</td>
                            <td>{{ $purchase->due_amount }}</td>
                            <td>
                                <span class="badge badge-{{ $purchase->payment_status === 'paid' ? 'success' : ($purchase->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.purchases.edit', $purchase) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.purchases.returns.index', $purchase) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-undo"></i>
                                </a>
                                <form action="{{ route('admin.purchases.destroy', $purchase) }}" method="post" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
            $('#purchases-table').DataTable();

            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will reverse the stock and payment for this purchase.',
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
