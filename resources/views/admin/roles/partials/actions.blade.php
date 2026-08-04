@can('role.edit')
    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-warning">
        <i class="fas fa-edit"></i>
    </a>
@endcan
@can('role.delete')
    <form action="{{ route('admin.roles.destroy', $role) }}" method="post" class="d-inline"
          data-confirm="Delete this role?" data-confirm-text="This role will be permanently deleted."
          data-confirm-button="Delete">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="fas fa-trash"></i>
        </button>
    </form>
@endcan
