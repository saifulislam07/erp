@extends('layouts.admin')

@section('content_title', $labels['title'])

@section('content_body')
    <div class="row">
        <div class="col-6 col-md-4">
            <div class="stat-tile stat-tile--danger" id="tile-due">
                <span class="stat-tile__icon"><i class="fas fa-balance-scale"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">Total {{ strtolower($labels['balance']) }}</span>
                    <span class="stat-tile__value" id="total-due">—</span>
                </span>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="stat-tile stat-tile--muted">
                <span class="stat-tile__icon"><i class="fas fa-users"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">{{ $labels['parties'] }} with a balance</span>
                    <span class="stat-tile__value" id="with-balance">—</span>
                    <span class="stat-tile__meta" id="total-parties"></span>
                </span>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="stat-tile stat-tile--info">
                <span class="stat-tile__icon"><i class="fas fa-piggy-bank"></i></span>
                <span class="stat-tile__body">
                    <span class="stat-tile__label">Held as advance</span>
                    <span class="stat-tile__value" id="total-advance">—</span>
                </span>
            </div>
        </div>
    </div>

    <form method="get" class="filter-bar" id="balances-filter" data-no-submit-guard>
        <div class="row align-items-end">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="q">Search {{ strtolower($labels['parties']) }}</label>
                    <input type="text" name="q" id="q" class="form-control"
                        value="{{ request('q') }}" placeholder="Name, phone or ID">
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
                    <a href="#" class="btn btn-secondary" data-table-clear>Clear</a>
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
            <div class="table-responsive">
                <table id="balances-table" class="table table-hover mb-0" style="width: 100%">
                    <thead>
                        <tr>
                            <th>{{ $labels['party'] }}</th>
                            <th class="text-right">{{ $labels['billed'] }}</th>
                            @if ($labels['party'] === 'Supplier')
                                <th class="text-right">Returned</th>
                            @endif
                            <th class="text-right">{{ $labels['paid'] }}</th>
                            <th class="text-right">{{ $labels['balance'] }}</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            var columns = [
                { data: 'party_name', name: 'party_name' },
                { data: 'billed_value', name: 'billed', className: 'text-right' },
            ];

            @if ($labels['party'] === 'Supplier')
                columns.push({ data: 'returned_value', name: 'returned', className: 'text-right' });
            @endif

            columns.push(
                { data: 'paid_value', name: 'paid', className: 'text-right' },
                { data: 'balance_value', name: 'balance', className: 'text-right' },
                { data: 'actions', name: 'actions', orderable: false, className: 'text-right text-nowrap' }
            );

            ERP.serverTable('#balances-table', {
                url: '{{ route($routePrefix.'.index') }}',
                filter: '#balances-filter',
                empty: '{{ $labels['empty'] }}',
                order: [],
                columns: columns,
                options: {
                    // The filter bar drives this screen; the tiles above summarise
                    // the whole filtered set, not the page on screen.
                    searching: false,
                    ajax: {
                        dataSrc: function (json) {
                            $('#total-due').text(json.total_due);
                            $('#total-advance').text(json.total_advance);
                            $('#with-balance').text(json.with_balance);
                            $('#total-parties').text('of ' + json.total_parties + ' total');
                            $('#tile-due')
                                .toggleClass('stat-tile--danger', json.with_balance > 0)
                                .toggleClass('stat-tile--success', json.with_balance === 0);

                            return json.data;
                        }
                    }
                },
            });
        });
    </script>
@endpush
