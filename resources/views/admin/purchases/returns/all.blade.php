@extends('layouts.admin')

@section('content_title', 'Purchase returns')

@section('content_body')
    @include('admin.partials.return-filters', [
        'clearRoute' => route('admin.purchase-returns.index'),
        'searchLabel' => 'Return, purchase or supplier',
    ])

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $returns->count() }} {{ Str::plural('return', $returns->count()) }}
                &middot; {{ money($returns->sum(fn ($r) => (float) $r->total_amount)) }} returned
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-truck-loading mr-1"></i> Purchases
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($returns->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-undo"></i>
                    <p>
                        {{ array_filter($filters) ? 'Nothing matches this filter.' : 'No goods have been returned to a supplier yet.' }}
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table id="returns-table" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Return</th>
                                <th>Purchase</th>
                                <th>Supplier</th>
                                <th>Items</th>
                                <th>Reason</th>
                                <th class="text-right">Value</th>
                                <th class="text-right" data-orderable="false"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($returns as $return)
                                <tr>
                                    <td class="text-nowrap">{{ $return->return_date->format('d M Y') }}</td>
                                    <td>{{ $return->return_id }}</td>
                                    <td>
                                        @if ($return->purchase)
                                            <a href="{{ route('admin.purchases.show', $return->purchase) }}">
                                                {{ $return->purchase->purchase_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $return->purchase?->supplier?->name ?? '—' }}</td>
                                    <td>
                                        {{ $return->items->count() }} {{ Str::plural('line', $return->items->count()) }}
                                        <small class="d-block text-muted">
                                            {{ $return->items->take(2)->map(fn ($i) => $i->product?->name)->filter()->implode(', ') }}{{ $return->items->count() > 2 ? '…' : '' }}
                                        </small>
                                    </td>
                                    <td>{{ Str::limit($return->reason, 40) }}</td>
                                    <td class="text-right">{{ money($return->total_amount) }}</td>
                                    <td class="text-right text-nowrap">
                                        @if ($return->purchase)
                                            <a href="{{ route('admin.purchases.returns.index', $return->purchase) }}"
                                               class="btn btn-sm btn-secondary" title="View on purchase">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
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
            $('#returns-table').DataTable({ searching: false, order: [], pageLength: 25 });
        });
    </script>
@endpush
