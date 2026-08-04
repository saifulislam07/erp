{{ $return->items->count() }} {{ Str::plural('line', $return->items->count()) }}
<small class="d-block text-muted">
    {{ $return->items->take(2)->map(fn ($i) => $i->product?->name)->filter()->implode(', ') }}{{ $return->items->count() > 2 ? '…' : '' }}
</small>
