@php $client = $client ?? null; @endphp

@if ($client)
    <div class="form-group">
        <label>Unique ID</label>
        <input type="text" class="form-control" value="{{ $client->unique_id }}" disabled>
    </div>
@endif

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $client->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $client->email ?? '') }}">
    @error('email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="phone">Phone</label>
    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
        value="{{ old('phone', $client->phone ?? '') }}">
    @error('phone')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea name="address" id="address" rows="3"
        class="form-control @error('address') is-invalid @enderror">{{ old('address', $client->address ?? '') }}</textarea>
    @error('address')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="business_name">Business Name</label>
    <input type="text" name="business_name" id="business_name"
        class="form-control @error('business_name') is-invalid @enderror"
        value="{{ old('business_name', $client->business_name ?? '') }}">
    @error('business_name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="type">Type</label>
    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
        <option value="client" {{ old('type', $client->type ?? '') === 'client' ? 'selected' : '' }}>Client</option>
        <option value="agent" {{ old('type', $client->type ?? '') === 'agent' ? 'selected' : '' }}>Agent</option>
    </select>
    @error('type')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
