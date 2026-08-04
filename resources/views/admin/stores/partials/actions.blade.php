<a href="{{ route('admin.stores.edit', $store) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<form action="{{ route('admin.stores.destroy', $store) }}" method="post" class="d-inline"
      data-confirm="Delete this store?" data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
