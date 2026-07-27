@extends('layouts.admin')

@section('content_title', $party->name)

@section('content_body')
    @php
        $isSupplier = $labels['party'] === 'Supplier';
        $dateField = $isSupplier ? 'purchase_date' : 'sale_date';
        $refField = $isSupplier ? 'purchase_id' : 'sale_id';
        $showRoute = $isSupplier ? 'admin.purchases.show' : 'admin.sales.show';
        $invoiceRoute = $isSupplier ? 'admin.invoices.purchase' : 'admin.invoices.sale';
        $billed = $invoices->sum(fn ($i) => (float) $i->total_amount);
        $paid = $invoices->sum(fn ($i) => (float) $i->paid_amount);
        $advance = $payments->sum(fn ($p) => $p->unallocated_amount);
    @endphp

    <div class="card">
        <div class="card-body page-actions">
            <a href="{{ route($routePrefix.'.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> {{ $labels['parties'] }}
            </a>
            <span class="badge badge-soft-muted">{{ $party->unique_id }}</span>
            @if ($party->phone)
                <span class="text-muted"><i class="fas fa-phone fa-xs mr-1"></i>{{ $party->phone }}</span>
            @endif
            @if ($party->email)
                <span class="text-muted"><i class="fas fa-envelope fa-xs mr-1"></i>{{ $party->email }}</span>
            @endif

            <a href="{{ route($routePrefix.'.create', $party->id) }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-money-bill-wave mr-1"></i> {{ $labels['action'] }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-6 col-md-3">
            <div class="stat-tile stat-tile--muted">
                <span class="stat-tile__icon"><i class="fas fa-file-invoice"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">{{ $labels['billed'] }}</span>
                    <span class="stat-tile__value">{{ money($billed) }}</span>
                    <span class="stat-tile__meta">{{ $invoices->count() }} {{ Str::plural('invoice', $invoices->count()) }}</span>
                </span>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-tile stat-tile--success">
                <span class="stat-tile__icon"><i class="fas fa-check-circle"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">{{ $labels['paid'] }}</span>
                    <span class="stat-tile__value">{{ money($paid) }}</span>
                </span>
            </div>
        </div>

        @if ($isSupplier)
            <div class="col-6 col-md-3">
                <div class="stat-tile stat-tile--info">
                    <span class="stat-tile__icon"><i class="fas fa-undo"></i></span>
                    <span class="stat-tile__body">
                        <span class="stat-tile__label">Returned</span>
                        <span class="stat-tile__value">
                            {{ money($invoices->sum(fn ($i) => (float) $i->returns->sum('total_amount'))) }}
                        </span>
                    </span>
                </div>
            </div>
        @else
            <div class="col-6 col-md-3">
                <div class="stat-tile stat-tile--info">
                    <span class="stat-tile__icon"><i class="fas fa-piggy-bank"></i></span>
                    <span class="stat-tile__body">
                        <span class="stat-tile__label">Advance held</span>
                        <span class="stat-tile__value">{{ money($advance) }}</span>
                    </span>
                </div>
            </div>
        @endif

        <div class="col-6 col-md-3">
            <div class="stat-tile {{ $balance > 0.009 ? 'stat-tile--danger' : 'stat-tile--success' }}">
                <span class="stat-tile__icon"><i class="fas fa-balance-scale"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">{{ $labels['balance'] }}</span>
                    <span class="stat-tile__value">{{ money(max(0, $balance)) }}</span>
                    @if ($balance <= 0.009)
                        <span class="stat-tile__meta">{{ $labels['settled'] }}</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- --------------------------------------------------------- invoices --}}
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $labels['invoices'] }}</h3>
                </div>

                <div class="card-body p-0">
                    @if ($invoices->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <p>No {{ strtolower($labels['invoices']) }} recorded for this {{ strtolower($labels['party']) }}.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">{{ $labels['paid'] }}</th>
                                        <th class="text-right">Due</th>
                                        <th class="text-right" style="width: 72px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        <tr>
                                            <td class="text-nowrap">{{ $invoice->{$dateField}?->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route($showRoute, $invoice) }}">{{ $invoice->{$refField} }}</a>
                                            </td>
                                            <td class="text-right">{{ money($invoice->total_amount) }}</td>
                                            <td class="text-right">{{ money($invoice->paid_amount) }}</td>
                                            <td class="text-right">
                                                @if ((float) $invoice->due_amount > 0.009)
                                                    <strong class="text-danger">{{ money($invoice->due_amount) }}</strong>
                                                @else
                                                    <span class="badge badge-soft-success">Paid</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route($invoiceRoute, $invoice) }}" target="_blank"
                                                   class="btn btn-sm btn-secondary" title="Invoice PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- --------------------------------------------------------- payments --}}
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $labels['payments'] }}</h3>
                </div>

                <div class="card-body p-0">
                    @if ($payments->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-money-bill-wave"></i>
                            <p>Nothing recorded yet.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Method</th>
                                        <th class="text-right">Amount</th>
                                        @if (auth()->user()->is_admin)
                                            <th style="width: 40px"></th>
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
                                                @if ($payment->unallocated_amount > 0.009)
                                                    <span class="badge badge-soft-info">
                                                        {{ money($payment->unallocated_amount) }} advance
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
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
        </div>
    </div>
@endsection
