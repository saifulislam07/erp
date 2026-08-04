@extends('layouts.admin')

@section('content_title', 'Returns')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.returns.index') }}" method="get" id="returns-filter" data-no-submit-guard>
            <div class="card-body row">
                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Return ID</label>
                    <input type="text" name="return_id" class="form-control" value="{{ request('return_id') }}">
                </div>
                <div class="col-md-2">
                    <label>Client Name</label>
                    <input type="text" name="client_name" class="form-control" value="{{ request('client_name') }}">
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
                    <a href="#" class="btn btn-secondary ml-1" data-table-clear>Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="returns-count">All Returns</h3>
            <div class="card-tools">
                <a href="{{ route('admin.return-types.index') }}" class="btn btn-secondary btn-sm">Return Types</a>
            </div>
        </div>
        <div class="card-body">
            <table id="returns-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Return ID</th>
                        <th>Order</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Refund</th>
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
            ERP.serverTable('#returns-table', {
                url: '{{ route('admin.returns.index') }}',
                filter: '#returns-filter',
                count: '#returns-count',
                noun: 'return',
                empty: 'No returns requested yet.',
                order: [[0, 'desc']],
                columns: [
                    { data: 'return_id', name: 'return_id' },
                    { data: 'order_label', name: 'order_label' },
                    { data: 'client_name', name: 'client_name' },
                    { data: 'type_name', name: 'type_name' },
                    { data: 'state', name: 'state' },
                    { data: 'refund', name: 'refund', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
            });
        });
    </script>
@endpush
