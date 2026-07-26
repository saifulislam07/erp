@extends('layouts.admin')

@section('content_title', 'Client Feedback')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.feedbacks.index') }}" method="get">
            <div class="card-body row">
                <div class="col-md-2">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <option value="">-- All --</option>
                        <option value="product" {{ request('type') === 'product' ? 'selected' : '' }}>Product</option>
                        <option value="delivery" {{ request('type') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                        <option value="agent" {{ request('type') === 'agent' ? 'selected' : '' }}>Agent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Rating</label>
                    <select name="rating" class="form-control">
                        <option value="">-- All --</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ (int) request('rating') === $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Feedback</h3>
        </div>
        <div class="card-body">
            <table id="feedback-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feedbacks as $feedback)
                        <tr>
                            <td>{{ $feedback->order->order_id }}</td>
                            <td>{{ $feedback->client->name }}</td>
                            <td>{{ ucfirst($feedback->type) }}</td>
                            <td>{{ $feedback->rating }} / 5</td>
                            <td>{{ $feedback->comment }}</td>
                            <td>{{ $feedback->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () { $('#feedback-table').DataTable(); });
    </script>
@endpush
