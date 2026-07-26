@php $unit = $unit ?? null; @endphp

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $unit->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="symbol">Symbol</label>
    <input type="text" name="symbol" id="symbol" class="form-control @error('symbol') is-invalid @enderror"
        value="{{ old('symbol', $unit->symbol ?? '') }}">
    @error('symbol')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
