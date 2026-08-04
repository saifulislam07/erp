<a href="{{ route('admin.stocks.edit', $stock) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<form action="{{ route('admin.stocks.destroy', $stock) }}" method="post" class="d-inline"
      data-confirm="Delete this stock entry?" data-confirm-text="This stock entry will be removed."
      data-confirm-button="Yes, remove it">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
