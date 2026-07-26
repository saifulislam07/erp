@php $store = $store ?? null; @endphp

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $store->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="location">Location</label>
    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror"
        value="{{ old('location', $store->location ?? '') }}">
    @error('location')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="3"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $store->description ?? '') }}</textarea>
    @error('description')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group form-check">
    <input type="hidden" name="status" value="0">
    <input type="checkbox" name="status" id="status" class="form-check-input" value="1"
        {{ old('status', $store->status ?? true) ? 'checked' : '' }}>
    <label for="status" class="form-check-label">Active</label>
</div>
