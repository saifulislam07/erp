@extends('layouts.admin')

@section('content_title', 'Edit Purchase Return')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Return {{ $return->return_id }}</h3>
        </div>

        <form action="{{ route('admin.purchases.returns.update', [$purchase, $return]) }}" method="post">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="return_date">Return Date</label>
                            <input type="date" name="return_date" id="return_date"
                                class="form-control @error('return_date') is-invalid @enderror"
                                value="{{ old('return_date', $return->return_date->format('Y-m-d')) }}">
                            @error('return_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <input type="text" name="reason" id="reason"
                                class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', $return->reason) }}">
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Purchased Qty</th>
                            <th>Remaining (excl. this return)</th>
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
                                <td>{{ $row->remaining_qty }}</td>
                                <td>{{ $row->unit_price }}</td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="{{ $row->remaining_qty }}"
                                        name="items[{{ $i }}][quantity]" class="form-control" value="{{ old("items.$i.quantity", $row->current_quantity) }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Return</button>
                <a href="{{ route('admin.purchases.returns.index', $purchase) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
