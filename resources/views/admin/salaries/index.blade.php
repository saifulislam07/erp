@extends('layouts.admin')

@section('content_title', 'Salaries')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Salary Payments</h3>
            <div class="card-tools">
                <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary btn-sm">Pay Salary</a>
            </div>
        </div>
        <div class="card-body">
            <table id="salaries-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Month</th>
                        <th>Basic</th>
                        <th>Deduction</th>
                        <th>Net</th>
                        <th>Method</th>
                        <th>Paid At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salaries as $salary)
                        <tr>
                            <td>{{ $salary->user->name }}</td>
                            <td>{{ $salary->month }}</td>
                            <td>{{ $salary->basic_salary }}</td>
                            <td>{{ $salary->deduction }}</td>
                            <td>{{ $salary->net_salary }}</td>
                            <td>{{ ucfirst($salary->payment_method) }}</td>
                            <td>{{ $salary->paid_at?->format('Y-m-d') }}</td>
                            <td><a href="{{ route('admin.salaries.show', $salary) }}" class="btn btn-sm btn-info">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () { $('#salaries-table').DataTable(); });
    </script>
@endpush
