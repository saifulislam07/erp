@extends('layouts.admin')

@section('content_title', 'Transfer History')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Transfers</h3>
            <div class="card-tools">
                <a href="{{ route('admin.cash-bank.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            @if ($transfers->isEmpty())
                <p class="text-muted mb-0">No transfers recorded yet.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Transfer ID</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Note</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transfers as $transfer)
                            <tr>
                                <td>{{ $transfer->transfer_id }}</td>
                                <td>{{ ucfirst($transfer->from_method) }}</td>
                                <td>{{ ucfirst($transfer->to_method) }}</td>
                                <td>{{ $transfer->amount }}</td>
                                <td>{{ $transfer->transfer_date->format('Y-m-d') }}</td>
                                <td>{{ $transfer->note }}</td>
                                <td>{{ $transfer->creator?->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
