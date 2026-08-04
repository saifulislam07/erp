<a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<form action="{{ route('admin.departments.destroy', $department) }}" method="post" class="d-inline"
      data-confirm="Delete this department?" data-confirm-text="This department will be soft deleted."
      data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
