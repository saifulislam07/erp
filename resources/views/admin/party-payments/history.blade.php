@extends('layouts.admin')

@section('content_title', $labels['payments'])

@section('content_body')
    <form method="get" class="filter-bar" data-no-submit-guard>
        <div class="row align-items-end">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control"
                        value="{{ $filters['from_date'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="to_date">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control"
                        value="{{ $filters['to_date'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="method">Method</label>
                    <select name="method" id="method" class="form-control">
                        <option value="">Any method</option>
                        @foreach (['cash' => 'Cash', 'bank' => 'Bank', 'mobile_banking' => 'Mobile banking'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['method'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group page-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route($routePrefix.'.history') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $payments->count() }} {{ Str::plural('payment', $payments->count()) }}
                &middot; {{ money($payments->sum(fn ($p) => (float) $p->amount)) }}
            </h3>
            <div class="card-tools">
                <a href="{{ route($routePrefix.'.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-balance-scale mr-1"></i> {{ $labels['title'] }}
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($payments->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-money-bill-wave"></i>
                    <p>No payments recorded for this period.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table id="payments-table" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>{{ $labels['party'] }}</th>
                                <th>Method</th>
                                <th>Applied to</th>
                                <th class="text-right">Amount</th>
                                @if (auth()->user()->is_admin)
                                    <th class="text-right" data-orderable="false"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td class="text-nowrap">{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td>
                                        {{ $payment->payment_id }}
                                        @if ($payment->reference)
                                            <small class="d-block text-muted">{{ $payment->reference }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route($routePrefix.'.ledger', $payment->party_id) }}">
                                            {{ $names[$payment->party_id] ?? 'Deleted '.strtolower($labels['party']) }}
                                        </a>
                                    </td>
                                    <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
                                    <td>
                                        @if ($payment->allocations->isEmpty())
                                            <span class="badge badge-soft-info">Advance</span>
                                        @else
                                            {{ $payment->allocations->count() }}
                                            {{ Str::plural('invoice', $payment->allocations->count()) }}
                                            @if ($payment->unallocated_amount > 0.009)
                                                <span class="badge badge-soft-info">
                                                    +{{ money($payment->unallocated_amount) }} advance
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-right">{{ money($payment->amount) }}</td>
                                    @if (auth()->user()->is_admin)
                                        <td class="text-right">
                                            <form action="{{ route($routePrefix.'.destroy', $payment) }}" method="post"
                                                  class="d-inline"
                                                  data-confirm="Reverse this payment?"
                                                  data-confirm-text="{{ money($payment->amount) }} will be put back on the invoices it settled and removed from the cash/bank ledger."
                                                  data-confirm-button="Reverse">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Reverse">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('#payments-table').DataTable({ order: [], pageLength: 25 });
        });
    </script>
@endpush
