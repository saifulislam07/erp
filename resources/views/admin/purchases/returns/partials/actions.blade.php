@if ($return->purchase)
    <a href="{{ route('admin.purchases.returns.index', $return->purchase) }}"
       class="btn btn-sm btn-secondary" title="View on purchase">
        <i class="fas fa-eye"></i>
    </a>
@endif
