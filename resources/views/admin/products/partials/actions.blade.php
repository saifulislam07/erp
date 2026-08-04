<a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-secondary" title="View">
    <i class="fas fa-eye"></i>
</a>
<a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-secondary" title="Edit">
    <i class="fas fa-edit"></i>
</a>
<a href="{{ route('admin.products.discounts.index', $product) }}" class="btn btn-sm btn-secondary" title="Discounts">
    <i class="fas fa-tags"></i>
</a>
<form action="{{ route('admin.products.destroy', $product) }}" method="post" class="d-inline"
      data-confirm="Delete this product?" data-confirm-text="This product will be soft deleted."
      data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
        <i class="fas fa-trash"></i>
    </button>
</form>
