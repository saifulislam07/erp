@can('invoice.view')
    <a href="{{ route('admin.invoices.expense', $expense) }}" class="btn btn-sm btn-secondary"
       target="_blank" title="View invoice">
        <i class="fas fa-file-invoice"></i>
    </a>
@endcan
<a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.expenses.destroy', $expense) }}" method="post" class="d-inline"
      data-confirm="Delete this expense?"
      data-confirm-text="This will reverse the cash/bank debit for this expense."
      data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
</form>
