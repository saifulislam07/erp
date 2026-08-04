@extends('layouts.admin')

@section('content_title', $labels['payments'])

@section('content_body')
    <form method="get" class="filter-bar" id="payments-filter" data-no-submit-guard>
        <div class="row align-items-end">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control"
                        value="{{ request('from_date') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="to_date">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control"
                        value="{{ request('to_date') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="method">Method</label>
                    <select name="method" id="method" class="form-control">
                        <option value="">Any method</option>
                        @foreach (['cash' => 'Cash', 'bank' => 'Bank', 'mobile_banking' => 'Mobile banking'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
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
            <h3 class="card-title" id="payments-summary">Loading…</h3>
            <div class="card-tools">
                <a href="{{ route($routePrefix.'.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-balance-scale mr-1"></i> {{ $labels['title'] }}
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="payments-table" class="table table-hover mb-0" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>{{ $labels['party'] }}</th>
                            <th>Method</th>
                            <th>Applied to</th>
                            <th class="text-right">Amount</th>
                            @if (auth()->user()->is_admin)
                                <th class="text-right"></th>
                            @endif
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
            var paidValue = '';

            var columns = [
                { data: 'paid_on', name: 'paid_on', className: 'text-nowrap', searchable: false },
                { data: 'reference_cell', name: 'reference_cell' },
                { data: 'party_name', name: 'party_name', searchable: false },
                { data: 'method', name: 'method', searchable: false },
                { data: 'applied_to', name: 'applied_to', orderable: false, searchable: false },
                { data: 'amount', name: 'amount', className: 'text-right', searchable: false },
            ];

            @if (auth()->user()->is_admin)
                columns.push({ data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' });
            @endif

            var table = ERP.serverTable('#payments-table', {
                url: '{{ route($routePrefix.'.history') }}',
                filter: '#payments-filter',
                empty: 'No payments recorded for this period.',
                order: [[0, 'desc']],
                columns: columns,
                options: {
                    ajax: {
                        dataSrc: function (json) {
                            paidValue = json.paid_value;

                            return json.data;
                        }
                    }
                },
            });

            table.on('draw.dt', function () {
                var total = table.page.info().recordsDisplay;

                $('#payments-summary').text(
                    total + ' ' + (total === 1 ? 'payment' : 'payments') + ' · ' + paidValue
                );
            });
        });
    </script>
@endpush
