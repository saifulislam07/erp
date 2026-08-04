<form action="{{ route('admin.employees.toggle-status', $employee) }}" method="post">
    @csrf
    <button type="submit" class="btn btn-sm {{ $employee->status ? 'btn-success' : 'btn-secondary' }}">
        {{ $employee->status ? 'Active' : 'Inactive' }}
    </button>
</form>
