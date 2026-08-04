<a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-primary">
    <i class="fas fa-eye"></i>
</a>
<a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-warning">
    <i class="fas fa-edit"></i>
</a>
<form action="{{ route('admin.clients.reset-password', $client) }}" method="post" class="d-inline"
      data-confirm="Reset password?"
      data-confirm-text="A new random password will be generated and emailed to the client."
      data-confirm-button="Yes, reset it" data-confirm-danger="0">
    @csrf
    <button type="submit" class="btn btn-sm btn-info">
        <i class="fas fa-key"></i>
    </button>
</form>
<form action="{{ route('admin.clients.destroy', $client) }}" method="post" class="d-inline"
      data-confirm="Delete this client?" data-confirm-text="This client will be soft deleted."
      data-confirm-button="Delete">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
