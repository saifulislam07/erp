<a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-sm btn-info">
    <i class="fas fa-eye"></i>
</a>
<a href="{{ route('admin.purchases.edit', $purchase) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<a href="{{ route('admin.purchases.returns.index', $purchase) }}" class="btn btn-sm btn-secondary">
    <i class="fas fa-undo"></i>
</a>
<form action="{{ route('admin.purchases.destroy', $purchase) }}" method="post" class="d-inline"
      data-confirm="Delete this purchase?"
      data-confirm-text="This will reverse the stock and payment for this purchase."
      data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
