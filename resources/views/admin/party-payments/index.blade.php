@extends('layouts.admin')

@section('content_title', $labels['title'])

@section('content_body')
    @php
        $totalDue = $rows->sum('balance');
        $overdueCount = $rows->filter(fn ($row) => $row->balance > 0.009)->count();
        $totalAdvance = $rows->sum('advance');
    @endphp

    <div class="row">
        <div class="col-6 col-md-4">
            <div class="stat-tile {{ $totalDue > 0 ? 'stat-tile--danger' : 'stat-tile--success' }}">
                <span class="stat-tile__icon"><i class="fas fa-balance-scale"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">Total {{ strtolower($labels['balance']) }}</span>
                    <span class="stat-tile__value">{{ money($totalDue) }}</span>
                </span>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="stat-tile stat-tile--muted">
                <span class="stat-tile__icon"><i class="fas fa-users"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">{{ $labels['parties'] }} with a balance</span>
                    <span class="stat-tile__value">{{ number_format($overdueCount) }}</span>
                    <span class="stat-tile__meta">of {{ $rows->count() }} total</span>
                </span>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="stat-tile stat-tile--info">
                <span class="stat-tile__icon"><i class="fas fa-piggy-bank"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">Held as advance</span>
                    <span class="stat-tile__value">{{ money($totalAdvance) }}</span>
                </span>
            </div>
        </div>
    </div>

    <form method="get" class="filter-bar" data-no-submit-guard>
        <div class="row align-items-end">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="q">Search {{ strtolower($labels['parties']) }}</label>
                    <input type="text" name="q" id="q" class="form-control"
                        value="{{ $search }}" placeholder="Name, phone or ID">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="outstanding" value="1" id="outstanding"
                            class="custom-control-input" @checked(request()->boolean('outstanding'))>
                        <label for="outstanding" class="custom-control-label">Only those with a balance</label>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group page-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    @if ($search || request()->boolean('outstanding'))
                        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $labels['parties'] }}</h3>
            <div class="card-tools">
                <a href="{{ route($routePrefix.'.history') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-history mr-1"></i> {{ $labels['payments'] }}
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($rows->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-user-friends"></i>
                    <p>{{ $search || request()->boolean('outstanding') ? 'Nothing matches this filter.' : $labels['empty'] }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table id="balances-table" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ $labels['party'] }}</th>
                                <th class="text-right">{{ $labels['billed'] }}</th>
                                @if ($labels['party'] === 'Supplier')
                                    <th class="text-right">Returned</th>
                                @endif
                                <th class="text-right">{{ $labels['paid'] }}</th>
                                <th class="text-right">{{ $labels['balance'] }}</th>
                                <th class="text-right" data-orderable="false">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route($routePrefix.'.ledger', $row->party->id) }}">
                                            {{ $row->party->name }}
                                        </a>
                                        <small class="d-block text-muted">
                                            {{ $row->party->unique_id }}
                                            @if ($row->party->phone) &middot; {{ $row->party->phone }} @endif
                                        </small>
                                    </td>
                                    <td class="text-right">{{ money($row->billed) }}</td>
                                    @if ($labels['party'] === 'Supplier')
                                        <td class="text-right">
                                            {{ $row->returned > 0 ? money($row->returned) : '—' }}
                                        </td>
                                    @endif
                                    <td class="text-right">
                                        {{ money($row->paid) }}
                                        @if ($row->advance > 0.009)
                                            <small class="d-block text-muted">{{ money($row->advance) }} advance</small>
                                        @endif
                                    </td>
                                    <td class="text-right" data-order="{{ $row->balance }}">
                                        @if ($row->balance > 0.009)
                                            <strong class="text-danger">{{ money($row->balance) }}</strong>
                                        @elseif ($row->balance < -0.009)
                                            <span class="text-success">{{ money(abs($row->balance)) }} in credit</span>
                                        @else
                                            <span class="badge badge-soft-success">{{ $labels['settled'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a href="{{ route($routePrefix.'.ledger', $row->party->id) }}"
                                           class="btn btn-sm btn-secondary" title="Statement">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        <a href="{{ route($routePrefix.'.create', $row->party->id) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-money-bill-wave mr-1"></i> {{ $labels['action'] }}
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
@endsection

@push('js')
    <script>
        $(function () {
            $('#balances-table').DataTable({
                searching: false,
                order: [],
                pageLength: 25,
            });
        });
    </script>
@endpush
