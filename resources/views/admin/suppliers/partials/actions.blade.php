<a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="post" class="d-inline"
      data-confirm="Delete this supplier?" data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
