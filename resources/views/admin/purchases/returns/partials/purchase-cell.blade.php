@if ($return->purchase)
    <a href="{{ route('admin.purchases.show', $return->purchase) }}">{{ $return->purchase->purchase_id }}</a>
@else
    <span class="text-muted">—</span>
@endif
