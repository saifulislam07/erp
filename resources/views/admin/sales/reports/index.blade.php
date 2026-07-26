@extends('layouts.admin')

@section('content_title', 'Sale Report')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.sales.report') }}" method="get">
            <div class="card-body row">
                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <label>Customer Type</label>
                    <select name="customer_type" class="form-control">
                        <option value="">-- All --</option>
                        <option value="local" {{ request('customer_type') === 'local' ? 'selected' : '' }}>Local</option>
                        <option value="client_agent" {{ request('customer_type') === 'client_agent' ? 'selected' : '' }}>Client/Agent</option>
                    </select>
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
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('admin.sales.report.excel', request()->query()) }}" class="btn btn-success mr-2">Excel</a>
                    <a href="{{ route('admin.sales.report.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Subtotal</th>
                        <th>Discount</th>
                        <th>VAT</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td>{{ $sale->sale_id }}</td>
                            <td>{{ $sale->customer_type === 'local' ? $sale->customer_name : $sale->customer?->name }}</td>
                            <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                            <td>{{ $sale->subtotal }}</td>
                            <td>{{ $sale->discount_amount }}</td>
                            <td>{{ $sale->vat_amount }}</td>
                            <td>{{ $sale->total_amount }}</td>
                            <td>{{ $sale->paid_amount }}</td>
                            <td>{{ $sale->due_amount }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Totals</th>
                        <th>{{ number_format($totals['subtotal'], 2) }}</th>
                        <th>{{ number_format($totals['discount_amount'], 2) }}</th>
                        <th>{{ number_format($totals['vat_amount'], 2) }}</th>
                        <th>{{ number_format($totals['total_amount'], 2) }}</th>
                        <th>{{ number_format($totals['paid_amount'], 2) }}</th>
                        <th>{{ number_format($totals['due_amount'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
