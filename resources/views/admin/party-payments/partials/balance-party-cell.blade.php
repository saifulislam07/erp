<a href="{{ route($routePrefix.'.ledger', $row->party->id) }}">{{ $row->party->name }}</a>
<small class="d-block text-muted">
    {{ $row->party->unique_id }}
    @if ($row->party->phone) &middot; {{ $row->party->phone }} @endif
</small>
