@extends('layouts.admin')

@section('content_title', $labels['action'].' — '.$party->name)

@section('content_body')
    @php
        $isSupplier = $labels['party'] === 'Supplier';
        $dateField = $isSupplier ? 'purchase_date' : 'sale_date';
        $refField = $isSupplier ? 'purchase_id' : 'sale_id';
    @endphp

    <form action="{{ route($routePrefix.'.store', $party->id) }}" method="post">
        @csrf

        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $labels['action'] }}</h3>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>{{ $party->name }}</strong> —
                            {{ strtolower($labels['balance']) }} <strong>{{ money(max(0, $balance)) }}</strong>.
                            <small class="d-block mt-1">{{ $labels['direction'] }}</small>
                        </div>

                        <div class="row">
                            <div class="col-sm-7">
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ trim(\App\Support\Branding::currency()) }}</span>
                                        </div>
                                        <input type="number" step="0.01" min="0.01" required autofocus
                                            name="amount" id="amount"
                                            class="form-control @error('amount') is-invalid @enderror"
                                            value="{{ old('amount', $balance > 0 ? number_format($balance, 2, '.', '') : '') }}">
                                    </div>
                                    @error('amount')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    @if ($balance > 0)
                                        <small class="form-text text-muted">
                                            <a href="#" id="pay-full">Use the full balance ({{ money($balance) }})</a>
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label for="payment_date">Date</label>
                                    <input type="date" name="payment_date" id="payment_date" required
                                        max="{{ today()->toDateString() }}"
                                        class="form-control @error('payment_date') is-invalid @enderror"
                                        value="{{ old('payment_date', today()->toDateString()) }}">
                                    @error('payment_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="method">Method</label>
                            <select name="method" id="method" required
                                class="form-control @error('method') is-invalid @enderror">
                                @foreach (['cash' => 'Cash', 'bank' => 'Bank', 'mobile_banking' => 'Mobile banking'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('method', 'cash') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" name="reference" id="reference" maxlength="100"
                                class="form-control @error('reference') is-invalid @enderror"
                                value="{{ old('reference') }}"
                                placeholder="Cheque or transaction number (optional)">
                            @error('reference')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="note">Note</label>
                            <textarea name="note" id="note" rows="2" maxlength="1000"
                                class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                            @error('note')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer page-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ $labels['action'] }}
                        </button>
                        <a href="{{ route($routePrefix.'.ledger', $party->id) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Open {{ strtolower($labels['invoices']) }}</h3>
                        <div class="card-tools">
                            <span class="badge badge-soft-muted">Settled oldest first</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if ($openInvoices->isEmpty())
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>
                                    Nothing outstanding. Anything you record now is kept as an advance
                                    and applied to the next invoice.
                                </p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0" id="open-invoices">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-right">Due</th>
                                            <th class="text-right">Settled by this</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($openInvoices as $invoice)
                                            <tr data-due="{{ (float) $invoice->due_amount }}">
                                                <td class="text-nowrap">{{ $invoice->{$dateField}?->format('d M Y') }}</td>
                                                <td>{{ $invoice->{$refField} }}</td>
                                                <td class="text-right">{{ money($invoice->total_amount) }}</td>
                                                <td class="text-right">{{ money($invoice->due_amount) }}</td>
                                                <td class="text-right allocation">—</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">Left over as advance</td>
                                            <td></td>
                                            <td class="text-right" id="leftover">—</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
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

            /*
             * Mirrors the server's allocation rule (oldest invoice first) so the
             * user can see exactly which invoices the amount will clear before
             * committing to it.
             */
            function previewAllocation() {
                let remaining = parseFloat($('#amount').val()) || 0;

                $('#open-invoices tbody tr').each(function () {
                    const due = parseFloat($(this).data('due')) || 0;
                    const applied = Math.min(remaining, due);

                    $(this).find('.allocation')
                        .text(applied > 0 ? formatMoney(applied) : '—')
                        .toggleClass('text-success', applied > 0);

                    remaining = Math.max(0, remaining - applied);
                });

                $('#leftover').text(remaining > 0 ? formatMoney(remaining) : '—');
            }

            $('#amount').on('input', previewAllocation);
            previewAllocation();

            $('#pay-full').on('click', function (event) {
                event.preventDefault();
                $('#amount').val(@json(round(max(0, $balance), 2))).trigger('input');
            });
        });
    </script>
@endpush
