@extends('layouts.admin')

@section('content_title', 'Sale returns')

@section('content_body')
    @include('admin.partials.return-filters', [
        'formId' => 'sale-returns-filter',
        'searchLabel' => 'Return, sale or customer',
    ])

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="sale-returns-summary">Loading…</h3>
            <div class="card-tools">
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-cash-register mr-1"></i> Sales
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="returns-table" class="table table-hover mb-0" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Return</th>
                            <th>Sale</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th class="text-right">Value</th>
                            <th class="text-right">Refunded</th>
                            <th>Stock</th>
                            <th class="text-right"></th>
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
            var returnedValue = '';
            var refundedValue = '';

            var table = ERP.serverTable('#returns-table', {
                url: '{{ route('admin.sale-returns.index') }}',
                filter: '#sale-returns-filter',
                empty: 'No sale has been returned yet. Open a sale and choose “Return items” to record one.',
                order: [[0, 'desc']],
                options: {
                    // The filter bar above is the only search on this screen, which
                    // is also what the server-side totals are calculated over.
                    searching: false,
                    ajax: {
                        dataSrc: function (json) {
                            returnedValue = json.returned_value;
                            refundedValue = json.refunded_value;

                            return json.data;
                        }
                    }
                },
                columns: [
                    { data: 'returned_on', name: 'returned_on', className: 'text-nowrap' },
                    { data: 'return_link', name: 'return_link' },
                    { data: 'sale_link', name: 'sale_link' },
                    { data: 'customer_name', name: 'customer_name', orderable: false },
                    { data: 'items_summary', name: 'items_summary', orderable: false },
                    { data: 'total_amount', name: 'total_amount', className: 'text-right' },
                    { data: 'refunded', name: 'refunded', className: 'text-right' },
                    { data: 'stock_state', name: 'stock_state' },
                    { data: 'actions', name: 'actions', orderable: false, className: 'text-right text-nowrap' },
                ],
            });

            table.on('draw.dt', function () {
                var total = table.page.info().recordsDisplay;

                $('#sale-returns-summary').text(
                    total + ' ' + (total === 1 ? 'return' : 'returns')
                    + ' · ' + returnedValue + ' returned'
                    + ' · ' + refundedValue + ' refunded'
                );
            });
        });
    </script>
@endpush
