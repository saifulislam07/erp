@php
    $employee = $employee ?? null;
    $currentRole = $employee?->roles->first()?->name;
@endphp

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $employee->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $employee->email ?? '') }}">
    @error('email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="phone">Phone</label>
    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
        value="{{ old('phone', $employee->phone ?? '') }}">
    @error('phone')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea name="address" id="address" rows="3"
        class="form-control @error('address') is-invalid @enderror">{{ old('address', $employee->address ?? '') }}</textarea>
    @error('address')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="department_id">Department</label>
    <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror">
        <option value="">-- None --</option>
        @foreach ($departments as $department)
            <option value="{{ $department->id }}"
                {{ (int) old('department_id', $employee->department_id ?? '') === $department->id ? 'selected' : '' }}>
                {{ $department->name }}
            </option>
        @endforeach
    </select>
    @error('department_id')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="role">Role</label>
    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror">
        <option value="">-- Select Role --</option>
        @foreach ($roles as $role)
            <option value="{{ $role }}" {{ old('role', $currentRole) === $role ? 'selected' : '' }}>
                {{ $role }}
            </option>
        @endforeach
    </select>
    @error('role')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
