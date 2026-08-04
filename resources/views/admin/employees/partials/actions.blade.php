<a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-sm btn-primary">
    <i class="fas fa-eye"></i>
</a>
<a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<button type="button" class="btn btn-sm btn-info reset-password-btn"
        data-url="{{ route('admin.employees.reset-password', $employee) }}">
    <i class="fas fa-key"></i>
</button>
<form action="{{ route('admin.employees.destroy', $employee) }}" method="post" class="d-inline"
      data-confirm="Delete this employee?" data-confirm-text="This employee will be soft deleted."
      data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
