@php $unit = $unit ?? null; @endphp

<div class="form-group">
    <label for="name">Unit name</label>
    <input type="text" name="name" id="name" required maxlength="255" autofocus
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $unit->name ?? '') }}"
        placeholder="e.g. Kilogram, Piece, Litre, Box">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
    <small class="form-text text-muted">
        This is how the unit appears on products, stock, purchases and sales.
    </small>
</div>
