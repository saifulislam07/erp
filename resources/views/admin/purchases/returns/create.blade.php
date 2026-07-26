@extends('layouts.admin')

@section('content_title', 'New Purchase Return')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Return for Purchase {{ $purchase->purchase_id }}</h3>
        </div>

        <form action="{{ route('admin.purchases.returns.store', $purchase) }}" method="post">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="return_date">Return Date</label>
                            <input type="date" name="return_date" id="return_date"
                                class="form-control @error('return_date') is-invalid @enderror"
                                value="{{ old('return_date', now()->format('Y-m-d')) }}">
                            @error('return_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <input type="text" name="reason" id="reason"
                                class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}">
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                @if ($rows->isEmpty())
                    <p class="text-muted">All items on this purchase have already been fully returned.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Purchased Qty</th>
                                <th>Already Returned</th>
                                <th>Remaining</th>
                                <th>Unit Price</th>
                                <th>Return Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $row)
                                <tr>
                                    <td>
                                        {{ $row->product_name }}
                                        <input type="hidden" name="items[{{ $i }}][purchase_item_id]" value="{{ $row->purchase_item_id }}">
                                    </td>
                                    <td>{{ $row->purchased_qty }}</td>
                                    <td>{{ $row->returned_qty }}</td>
                                    <td>{{ $row->remaining_qty }}</td>
                                    <td>{{ $row->unit_price }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ $row->remaining_qty }}"
                                            name="items[{{ $i }}][quantity]"
                                            class="form-control @error("items.$i.quantity") is-invalid @enderror"
                                            value="{{ old("items.$i.quantity", 0) }}">
                                        @error("items.$i.quantity")
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @error('items')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                @endif
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Submit Return</button>
                <a href="{{ route('admin.purchases.returns.index', $purchase) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
