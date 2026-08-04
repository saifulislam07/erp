<a href="{{ route('admin.sale-returns.show', $return) }}" class="btn btn-sm btn-secondary" title="View">
    <i class="fas fa-eye"></i>
</a>
@if (auth()->user()->is_admin)
    <form action="{{ route('admin.sale-returns.destroy', $return) }}" method="post" class="d-inline"
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
