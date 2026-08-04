<a href="{{ route($routePrefix.'.ledger', $row->party->id) }}" class="btn btn-sm btn-secondary" title="Statement">
    <i class="fas fa-file-alt"></i>
</a>
<a href="{{ route($routePrefix.'.create', $row->party->id) }}" class="btn btn-sm btn-primary">
    <i class="fas fa-money-bill-wave mr-1"></i> {{ $labels['action'] }}
</a>
