@extends('layouts.admin')

@section('content_title', 'Salaries')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="salaries-count">Salary Payments</h3>
            <div class="card-tools">
                <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary btn-sm">Pay Salary</a>
            </div>
        </div>
        <div class="card-body">
            <table id="salaries-table" class="table table-bordered table-striped" style="width: 100%">
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
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#salaries-table', {
                url: '{{ route('admin.salaries.index') }}',
                count: '#salaries-count',
                noun: 'salary payment',
                empty: 'No salary payments recorded yet.',
                order: [[1, 'desc']],
                columns: [
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'month', name: 'month' },
                    { data: 'basic_salary', name: 'basic_salary' },
                    { data: 'deduction', name: 'deduction' },
                    { data: 'net_salary', name: 'net_salary' },
                    { data: 'payment_method', name: 'payment_method' },
                    { data: 'paid_on', name: 'paid_on', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
            });
        });
    </script>
@endpush
