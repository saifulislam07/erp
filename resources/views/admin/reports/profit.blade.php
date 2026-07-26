@extends('layouts.admin')

@section('content_title', 'Profit Report')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.reports.profit') }}" method="get">
            <div class="card-body row">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-between">
                    @include('admin.partials.date-presets')
                    <div>
                        <button type="submit" class="btn btn-primary mr-2">Filter</button>
                        <a href="{{ route('admin.reports.profit.excel', request()->query()) }}" class="btn btn-success mr-2">Excel</a>
                        <a href="{{ route('admin.reports.profit.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Summary</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th>Gross Revenue</th><td>{{ number_format($summary['gross_revenue'], 2) }}</td></tr>
                <tr><th>Cost of Goods</th><td>{{ number_format($summary['cost_of_goods'], 2) }}</td></tr>
                <tr><th>Gross Profit</th><td>{{ number_format($summary['gross_profit'], 2) }}</td></tr>
                <tr><th>Total Expenses</th><td>{{ number_format($summary['total_expenses'], 2) }}</td></tr>
                <tr><th>VAT Collected</th><td>{{ number_format($summary['vat_collected'], 2) }}</td></tr>
                <tr class="table-success"><th>Net Profit</th><td><strong>{{ number_format($summary['net_profit'], 2) }}</strong></td></tr>
            </table>
        </div>
    </div>
@endsection
