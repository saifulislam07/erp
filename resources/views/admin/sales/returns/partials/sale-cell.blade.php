@if ($return->sale)
    <a href="{{ route('admin.sales.show', $return->sale) }}">{{ $return->sale->sale_id }}</a>
@else
    <span class="text-muted">—</span>
@endif
