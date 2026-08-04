<a href="{{ route($routePrefix.'.ledger', $payment->party_id) }}">
    {{ $name ?? 'Deleted '.strtolower($labels['party']) }}
</a>
