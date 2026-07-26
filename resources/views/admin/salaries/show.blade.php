@extends('layouts.admin')

@section('content_title', 'Salary Detail')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Salary for {{ $salary->user->name }} - {{ $salary->month }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.salaries.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Employee</th><td>{{ $salary->user->name }} ({{ $salary->user->employee_id }})</td></tr>
                <tr><th>Month</th><td>{{ $salary->month }}</td></tr>
                <tr><th>Basic Salary</th><td>{{ $salary->basic_salary }}</td></tr>
                <tr><th>Deduction</th><td>{{ $salary->deduction }}</td></tr>
                <tr><th>Net Salary</th><td>{{ $salary->net_salary }}</td></tr>
                <tr><th>Payment Method</th><td>{{ ucfirst($salary->payment_method) }}</td></tr>
                <tr><th>Paid At</th><td>{{ $salary->paid_at?->format('Y-m-d') }}</td></tr>
                <tr><th>Note</th><td>{{ $salary->note ?? '-' }}</td></tr>
                <tr><th>Processed By</th><td>{{ $salary->creator?->name }}</td></tr>
            </table>
        </div>
    </div>
@endsection
