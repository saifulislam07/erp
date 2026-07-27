@extends('layouts.admin')

@section('content_title', 'Sale returns')

@section('content_body')
    @include('admin.partials.return-filters', [
        'clearRoute' => route('admin.sale-returns.index'),
        'searchLabel' => 'Return, sale or customer',
    ])

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $returns->count() }} {{ Str::plural('return', $returns->count()) }}
                &middot; {{ money($returns->sum(fn ($r) => (float) $r->total_amount)) }} returned
                &middot; {{ money($returns->sum(fn ($r) => (float) $r->refund_amount)) }} refunded
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-cash-register mr-1"></i> Sales
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($returns->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-undo"></i>
                    <p>
                        @if (array_filter($filters))
                            Nothing matches this filter.
                        @else
                            No sale has been returned yet. Open a sale and choose “Return items” to record one.
                        @endif
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table id="returns-table" class="table table-hover mb-0">
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
                                <th class="text-right" data-orderable="false"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($returns as $return)
                                <tr>
                                    <td class="text-nowrap">{{ $return->return_date->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.sale-returns.show', $return) }}">{{ $return->return_id }}</a>
                                    </td>
                                    <td>
                                        @if ($return->sale)
                                            <a href="{{ route('admin.sales.show', $return->sale) }}">{{ $return->sale->sale_id }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $return->sale?->customer_name ?: 'Walk-in' }}</td>
                                    <td>
                                        {{ $return->items->count() }} {{ Str::plural('line', $return->items->count()) }}
                                        <small class="d-block text-muted">
                                            {{ $return->items->take(2)->map(fn ($i) => $i->product?->name)->filter()->implode(', ') }}{{ $return->items->count() > 2 ? '…' : '' }}
                                        </small>
                                    </td>
                                    <td class="text-right">{{ money($return->total_amount) }}</td>
                                    <td class="text-right">
                                        @if ((float) $return->refund_amount > 0)
                                            {{ money($return->refund_amount) }}
                                            <small class="d-block text-muted">
                                                {{ ucwords(str_replace('_', ' ', $return->refund_method)) }}
                                            </small>
                                        @else
                                            <span class="text-muted">Credited</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $return->restock ? 'badge-soft-success' : 'badge-soft-muted' }}">
                                            {{ $return->restock ? 'Restocked' : 'Written off' }}
                                        </span>
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a href="{{ route('admin.sale-returns.show', $return) }}"
                                           class="btn btn-sm btn-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if (auth()->user()->is_admin)
                                            <form action="{{ route('admin.sale-returns.destroy', $return) }}" method="post"
                                                  class="d-inline"
                                                  data-confirm="Delete this return?"
                                                  data-confirm-text="The restocked goods are taken back out and any refund is reversed in the cash ledger."
                                                  data-confirm-button="Delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
