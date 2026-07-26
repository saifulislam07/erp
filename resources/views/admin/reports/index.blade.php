@extends('layouts.admin')

@section('content_title', 'Reports & Invoices')

@section('content_body')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Sales Report</h3></div>
                <div class="card-body">Sales totals, discounts, VAT, and payment status.</div>
                <div class="card-footer"><a href="{{ route('admin.sales.report') }}" class="btn btn-primary btn-sm">Open</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Purchase Report</h3></div>
                <div class="card-body">Purchases by supplier with subtotal, VAT, and dues.</div>
                <div class="card-footer"><a href="{{ route('admin.purchases.report') }}" class="btn btn-primary btn-sm">Open</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Expense Report</h3></div>
                <div class="card-body">Expenses broken down by head, with grand total.</div>
                <div class="card-footer"><a href="{{ route('admin.expenses.report') }}" class="btn btn-primary btn-sm">Open</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">Profit Report</h3></div>
                <div class="card-body">Gross revenue, cost of goods, expenses, and net profit.</div>
                <div class="card-footer"><a href="{{ route('admin.reports.profit') }}" class="btn btn-success btn-sm">Open</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">Stock Report</h3></div>
                <div class="card-body">Current stock levels and value by product/store.</div>
                <div class="card-footer"><a href="{{ route('admin.reports.stock') }}" class="btn btn-success btn-sm">Open</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">Order Report</h3></div>
                <div class="card-body">Client orders filtered by status, client, and date.</div>
                <div class="card-footer"><a href="{{ route('admin.reports.orders') }}" class="btn btn-success btn-sm">Open</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Asset Report</h3></div>
                <div class="card-body">Asset register with purchase value and status.</div>
                <div class="card-footer"><a href="{{ route('admin.assets.report') }}" class="btn btn-secondary btn-sm">Open</a></div>
            </div>
        </div>
    </div>
@endsection
