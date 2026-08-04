<a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-info">
    <i class="fas fa-eye"></i>
</a>
@can('invoice.view')
    <a href="{{ route('admin.invoices.sale', $sale) }}" class="btn btn-sm btn-secondary"
       target="_blank" title="View invoice">
        <i class="fas fa-file-invoice"></i>
    </a>
@endcan
@can('update', $sale)
    <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-sm btn-warning">
        <i class="fas fa-edit"></i>
    </a>
@endcan
@can('delete', $sale)
    <form action="{{ route('admin.sales.destroy', $sale) }}" method="post" class="d-inline"
          data-confirm="Delete this sale?"
          data-confirm-text="This will reverse the stock and payment for this sale."
          data-confirm-button="Delete">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="fas fa-trash"></i>
        </button>
    </form>
@endcan
