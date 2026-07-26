@extends('layouts.client')

@section('title', 'Request Return')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Request Return for Order {{ $order->order_id }}</h3>
        </div>

        <form action="{{ route('client.returns.store', $order) }}" method="post">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="return_type_id">Return Type</label>
                    <select name="return_type_id" id="return_type_id" class="form-control @error('return_type_id') is-invalid @enderror">
                        <option value="">-- Select Type --</option>
                        @foreach ($returnTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('return_type_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reason">Reason</label>
                    <textarea name="reason" id="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                    @error('reason')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                @if ($rows->isEmpty())
                    <p class="text-muted">All items on this order have already been fully returned.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Ordered Qty</th>
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
                                        <input type="hidden" name="items[{{ $i }}][order_item_id]" value="{{ $row->order_item_id }}">
                                    </td>
                                    <td>{{ $row->ordered_qty }}</td>
                                    <td>{{ $row->remaining_qty }}</td>
                                    <td>{{ $row->unit_price }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ $row->remaining_qty }}"
                                            name="items[{{ $i }}][quantity]"
                                            class="form-control @error("items.$i.quantity") is-invalid @enderror" value="0">
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
                <button type="submit" class="btn btn-primary">Submit Return Request</button>
                <a href="{{ route('client.orders.show', $order) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
