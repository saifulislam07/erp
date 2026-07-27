@extends('layouts.admin')

@section('content_title', 'Purchase Report')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.purchases.report') }}" method="get">
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
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('admin.purchases.report.excel', request()->query()) }}" class="btn btn-success mr-2">Excel</a>
                    <a href="{{ route('admin.purchases.report.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Subtotal</th>
                        <th>VAT</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_id }}</td>
                            <td>{{ $purchase->supplier?->name }}</td>
                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                            <td>{{ $purchase->subtotal }}</td>
                            <td>{{ $purchase->vat_amount }}</td>
                            <td>{{ $purchase->total_amount }}</td>
                            <td>{{ $purchase->paid_amount }}</td>
                            <td>{{ $purchase->due_amount }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Totals</th>
                        <th>{{ money($totals['subtotal']) }}</th>
                        <th>{{ money($totals['vat_amount']) }}</th>
                        <th>{{ money($totals['total_amount']) }}</th>
                        <th>{{ money($totals['paid_amount']) }}</th>
                        <th>{{ money($totals['due_amount']) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
