@extends('layouts.admin')

@section('content_title', 'Purchase returns')

@section('content_body')
    @include('admin.partials.return-filters', [
        'formId' => 'purchase-returns-filter',
        'searchLabel' => 'Return, purchase or supplier',
    ])

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="purchase-returns-summary">Loading…</h3>
            <div class="card-tools">
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-truck-loading mr-1"></i> Purchases
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
                            <th>Purchase</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Reason</th>
                            <th class="text-right">Value</th>
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

            var table = ERP.serverTable('#returns-table', {
                url: '{{ route('admin.purchase-returns.index') }}',
                filter: '#purchase-returns-filter',
                empty: 'No goods have been returned to a supplier yet.',
                order: [[0, 'desc']],
                options: {
                    // The filter bar above is the only search on this screen, which
                    // is also what the server-side total is calculated over.
                    searching: false,
                    ajax: {
                        dataSrc: function (json) {
                            returnedValue = json.returned_value;

                            return json.data;
                        }
                    }
                },
                columns: [
                    { data: 'returned_on', name: 'returned_on', className: 'text-nowrap' },
                    { data: 'return_id', name: 'return_id' },
                    { data: 'purchase_link', name: 'purchase_link' },
                    { data: 'supplier_name', name: 'supplier_name', orderable: false },
                    { data: 'items_summary', name: 'items_summary', orderable: false },
                    { data: 'reason', name: 'reason' },
                    { data: 'total_amount', name: 'total_amount', className: 'text-right' },
                    { data: 'actions', name: 'actions', orderable: false, className: 'text-right text-nowrap' },
                ],
            });

            table.on('draw.dt', function () {
                var total = table.page.info().recordsDisplay;

                $('#purchase-returns-summary').text(
                    total + ' ' + (total === 1 ? 'return' : 'returns') + ' · ' + returnedValue + ' returned'
                );
            });
        });
    </script>
@endpush
