@extends('layouts.admin')

@section('content_title', 'Returns')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.returns.index') }}" method="get">
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
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Returns</h3>
            <div class="card-tools">
                <a href="{{ route('admin.return-types.index') }}" class="btn btn-secondary btn-sm">Return Types</a>
            </div>
        </div>
        <div class="card-body">
            <table id="returns-table" class="table table-bordered table-striped">
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
                <tbody>
                    @foreach ($returns as $return)
                        <tr>
                            <td>{{ $return->return_id }}</td>
                            <td>{{ $return->order->order_id }}</td>
                            <td>{{ $return->client->name }}</td>
                            <td>{{ $return->returnType->name }}</td>
                            <td>
                                <span class="badge badge-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$return->status] }}">
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>
                            <td>{{ $return->refund_amount ?? '-' }}</td>
                            <td><a href="{{ route('admin.returns.show', $return) }}" class="btn btn-sm btn-info">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () { $('#returns-table').DataTable(); });
    </script>
@endpush
