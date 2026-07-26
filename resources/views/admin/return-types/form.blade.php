@php $returnType = $returnType ?? null; @endphp

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $returnType->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="disposition">Disposition</label>
    <select name="disposition" id="disposition" class="form-control @error('disposition') is-invalid @enderror">
        <option value="restock" {{ old('disposition', $returnType->disposition ?? '') === 'restock' ? 'selected' : '' }}>Restock (add back to stock)</option>
        <option value="damage_section" {{ old('disposition', $returnType->disposition ?? '') === 'damage_section' ? 'selected' : '' }}>Damage Section (do not restock)</option>
    </select>
    @error('disposition')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $returnType->description ?? '') }}</textarea>
</div>
