<a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<form action="{{ route('admin.units.destroy', $unit) }}" method="post" class="d-inline"
      data-confirm="Delete this unit?" data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
