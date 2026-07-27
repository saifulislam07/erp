@extends('layouts.admin')

@section('content_title', 'Return items from sale '.$sale->sale_id)

@section('content_body')
    @if ($rows->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Every item on this sale has already been returned.</p>
                </div>
                <div class="text-center">
                    <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-secondary">Back to the sale</a>
                </div>
            </div>
        </div>
    @else
        <form action="{{ route('admin.sale-returns.store', $sale) }}" method="post">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Items being returned</h3>
                    <div class="card-tools">
                        <span class="badge badge-soft-muted">
                            {{ $sale->customer_name ?: $sale->customer?->name ?: 'Walk-in customer' }}
                            &middot; {{ $sale->sale_date->format('d M Y') }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="return-items">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Store</th>
                                    <th class="text-right">Sold</th>
                                    <th class="text-right">Already returned</th>
                                    <th class="text-right">Unit price</th>
                                    <th style="width: 140px">Return qty</th>
                                    <th class="text-right">Line value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $index => $row)
                                    <tr data-max="{{ $row->remaining_qty }}" data-price="{{ $row->unit_price }}">
                                        <td>
                                            {{ $row->product_name }}
                                            <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $row->sale_item_id }}">
                                        </td>
                                        <td>{{ $row->store_name ?? '—' }}</td>
                                        <td class="text-right">{{ qty($row->sold_qty) }}</td>
                                        <td class="text-right">{{ $row->returned_qty > 0 ? qty($row->returned_qty) : '—' }}</td>
                                        <td class="text-right">{{ money($row->unit_price) }}</td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="{{ $row->remaining_qty }}"
                                                name="items[{{ $index }}][quantity]"
                                                class="form-control form-control-sm return-qty text-right"
                                                value="{{ old("items.$index.quantity", 0) }}">
                                            <small class="form-text text-muted text-right mb-0">
                                                max {{ qty($row->remaining_qty) }}
                                            </small>
                                        </td>
                                        <td class="text-right line-total">{{ money(0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-right">Value of returned goods</td>
                                    <td class="text-right"><strong id="return-total">{{ money(0) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Return details</h3>
                        </div>

                        <div class="card-body">
                            <div class="form-group">
                                <label for="return_date">Return date</label>
                                <input type="date" name="return_date" id="return_date" required
                                    max="{{ today()->toDateString() }}"
                                    class="form-control @error('return_date') is-invalid @enderror"
                                    value="{{ old('return_date', today()->toDateString()) }}">
                                @error('return_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="reason">Reason</label>
                                <textarea name="reason" id="reason" rows="3" required maxlength="1000"
                                    class="form-control @error('reason') is-invalid @enderror"
                                    placeholder="Damaged in transit, wrong item shipped, customer changed their mind…">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="custom-control custom-switch mb-0">
                                <input type="hidden" name="restock" value="0">
                                <input type="checkbox" name="restock" id="restock" class="custom-control-input"
                                    value="1" @checked(old('restock', true))>
                                <label for="restock" class="custom-control-label">
                                    Put the goods back into stock
                                    <small class="d-block text-muted">
                                        Leave this off when the returned items are damaged and cannot be resold.
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Settlement</h3>
                        </div>

                        <div class="card-body">
                            <p class="text-muted">
                                By default the value of the return is credited against what this customer owes.
                                Enter an amount below only if money was handed back at the counter.
                            </p>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="refund_amount">Refunded now</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ trim(\App\Support\Branding::currency()) }}</span>
                                            </div>
                                            <input type="number" step="0.01" min="0"
                                                name="refund_amount" id="refund_amount"
                                                class="form-control @error('refund_amount') is-invalid @enderror"
                                                value="{{ old('refund_amount', 0) }}">
                                        </div>
                                        @error('refund_amount')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="refund_method">Paid out from</label>
                                        <select name="refund_method" id="refund_method"
                                            class="form-control @error('refund_method') is-invalid @enderror">
                                            <option value="">Not refunded</option>
                                            @foreach (['cash' => 'Cash', 'bank' => 'Bank', 'mobile_banking' => 'Mobile banking'] as $value => $label)
                                                <option value="{{ $value }}" @selected(old('refund_method') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('refund_method')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0" id="settlement-summary"></div>
                        </div>

                        <div class="card-footer page-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-undo mr-1"></i> Record return
                            </button>
                            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
@endsection

@push('js')
    <script>
        $(function () {
            const currency = @json(trim(\App\Support\Branding::currency()));

            function formatMoney(value) {
                return currency + ' ' + value.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            }

            function recalculate() {
                let total = 0;

                $('#return-items tbody tr').each(function () {
                    const $row = $(this);
                    const max = parseFloat($row.data('max')) || 0;
                    const price = parseFloat($row.data('price')) || 0;
                    const $input = $row.find('.return-qty');

                    let quantity = parseFloat($input.val()) || 0;

                    // Clamp here as well as server-side so the running total the
                    // user sees can never promise more than the return allows.
                    if (quantity > max) {
                        quantity = max;
                        $input.val(max);
                    }

                    const line = quantity * price;
                    total += line;
                    $row.find('.line-total').text(formatMoney(line));
                });

                $('#return-total').text(formatMoney(total));

                const refund = parseFloat($('#refund_amount').val()) || 0;
                const credited = Math.max(0, total - refund);
                const $summary = $('#settlement-summary');

                if (total <= 0) {
                    $summary.attr('class', 'alert alert-info mb-0')
                        .text('Enter a return quantity above to see how it settles.');
                } else if (refund > total) {
                    $summary.attr('class', 'alert alert-danger mb-0')
                        .text('The refund is larger than the value of the returned goods.');
                } else {
                    $summary.attr('class', 'alert alert-info mb-0').html(
                        '<strong>' + formatMoney(credited) + '</strong> credited against the customer’s balance' +
                        (refund > 0 ? ' and <strong>' + formatMoney(refund) + '</strong> paid back now.' : '.')
                    );
                }

                // A refund needs a source account; keep the two fields in step.
                $('#refund_method').prop('required', refund > 0);
            }

            $('#return-items').on('input', '.return-qty', recalculate);
            $('#refund_amount').on('input', recalculate);
            recalculate();
        });
    </script>
@endpush
