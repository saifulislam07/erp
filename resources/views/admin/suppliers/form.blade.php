@php $supplier = $supplier ?? null; @endphp

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $supplier->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $supplier->email ?? '') }}">
    @error('email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="phone">Phone</label>
    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
        value="{{ old('phone', $supplier->phone ?? '') }}">
    @error('phone')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea name="address" id="address" rows="3"
        class="form-control @error('address') is-invalid @enderror">{{ old('address', $supplier->address ?? '') }}</textarea>
    @error('address')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="company_name">Company Name</label>
    <input type="text" name="company_name" id="company_name"
        class="form-control @error('company_name') is-invalid @enderror"
        value="{{ old('company_name', $supplier->company_name ?? '') }}">
    @error('company_name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group form-check">
    <input type="hidden" name="status" value="0">
    <input type="checkbox" name="status" id="status" class="form-check-input" value="1"
        {{ old('status', $supplier->status ?? true) ? 'checked' : '' }}>
    <label for="status" class="form-check-label">Active</label>
</div>
