<a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<form action="{{ route('admin.categories.destroy', $category) }}" method="post" class="d-inline"
      data-confirm="Delete this category?" data-confirm-text="This category will be soft deleted."
      data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
