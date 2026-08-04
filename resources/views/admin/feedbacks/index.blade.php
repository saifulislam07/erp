@extends('layouts.admin')

@section('content_title', 'Client Feedback')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.feedbacks.index') }}" method="get" id="feedback-filter" data-no-submit-guard>
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
                    <a href="#" class="btn btn-secondary ml-1" data-table-clear>Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="feedback-count">All Feedback</h3>
        </div>
        <div class="card-body">
            <table id="feedback-table" class="table table-bordered table-striped" style="width: 100%">
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
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#feedback-table', {
                url: '{{ route('admin.feedbacks.index') }}',
                filter: '#feedback-filter',
                count: '#feedback-count',
                noun: 'feedback entry',
                empty: 'No feedback submitted yet.',
                order: [[5, 'desc']],
                columns: [
                    { data: 'order_label', name: 'order_label' },
                    { data: 'client_name', name: 'client_name' },
                    { data: 'type', name: 'type' },
                    { data: 'rating', name: 'rating' },
                    { data: 'comment', name: 'comment' },
                    { data: 'left_on', name: 'left_on', searchable: false },
                ],
            });
        });
    </script>
@endpush
