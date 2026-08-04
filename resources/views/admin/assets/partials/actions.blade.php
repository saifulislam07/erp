<a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.assets.destroy', $asset) }}" method="post" class="d-inline"
      data-confirm="Delete this asset?" data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
</form>
