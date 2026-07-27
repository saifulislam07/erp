@extends('layouts.admin')

@section('content_title', 'Return '.$return->return_id)

@section('content_body')
    <div class="card">
        <div class="card-body page-actions">
            <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> All sale returns
            </a>
            @if ($return->sale)
                <a href="{{ route('admin.sales.show', $return->sale) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-cash-register mr-1"></i> Sale {{ $return->sale->sale_id }}
                </a>
            @endif
            <span class="badge {{ $return->restock ? 'badge-soft-success' : 'badge-soft-muted' }}">
                {{ $return->restock ? 'Returned to stock' : 'Written off' }}
            </span>

            @if (auth()->user()->is_admin)
                <form action="{{ route('admin.sale-returns.destroy', $return) }}" method="post"
                      class="d-inline ml-auto"
                      data-confirm="Delete this return?"
                      data-confirm-text="The restocked goods are taken back out and any refund is reversed in the cash ledger."
                      data-confirm-button="Delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Returned items</h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Quantity</th>
                                    <th class="text-right">Unit price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($return->items as $item)
                                    <tr>
                                        <td>{{ $item->product?->name ?? 'Deleted product' }}</td>
                                        <td class="text-right">{{ qty($item->quantity) }}</td>
                                        <td class="text-right">{{ money($item->unit_price) }}</td>
                                        <td class="text-right">{{ money($item->total_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right">Value returned</td>
                                    <td class="text-right">{{ money($return->total_amount) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Details</h3>
                </div>

                <div class="card-body">
                    <dl class="detail-list">
                        <dt>Date</dt>
                        <dd>{{ $return->return_date->format('d M Y') }}</dd>

                        <dt>Customer</dt>
                        <dd>{{ $return->sale?->customer_name ?: $return->sale?->customer?->name ?: 'Walk-in' }}</dd>

                        <dt>Refunded at the counter</dt>
                        <dd>
                            @if ((float) $return->refund_amount > 0)
                                {{ money($return->refund_amount) }}
                                <span class="text-muted">
                                    via {{ ucwords(str_replace('_', ' ', $return->refund_method)) }}
                                </span>
                            @else
                                <span class="text-muted">Nothing paid out</span>
                            @endif
                        </dd>

                        <dt>Credited to the customer</dt>
                        <dd>{{ money($return->credited_amount) }}</dd>

                        <dt>Reason</dt>
                        <dd>{{ $return->reason }}</dd>

                        <dt>Recorded by</dt>
                        <dd>{{ $return->creator?->name ?? '—' }} &middot; {{ $return->created_at->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
