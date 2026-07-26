@php $discount = $discount ?? null; @endphp

<div class="form-group">
    <label for="discount_type">Discount Type</label>
    <select name="discount_type" id="discount_type" class="form-control @error('discount_type') is-invalid @enderror">
        <option value="percentage" {{ old('discount_type', $discount->discount_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage</option>
        <option value="fixed" {{ old('discount_type', $discount->discount_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed</option>
    </select>
    @error('discount_type')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="discount_value">Discount Value</label>
    <input type="number" step="0.01" name="discount_value" id="discount_value"
        class="form-control @error('discount_value') is-invalid @enderror"
        value="{{ old('discount_value', $discount->discount_value ?? '') }}">
    @error('discount_value')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date"
                class="form-control @error('start_date') is-invalid @enderror"
                value="{{ old('start_date', optional($discount?->start_date)->format('Y-m-d')) }}">
            @error('start_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="end_date">End Date (optional)</label>
            <input type="date" name="end_date" id="end_date"
                class="form-control @error('end_date') is-invalid @enderror"
                value="{{ old('end_date', optional($discount?->end_date)->format('Y-m-d')) }}">
            @error('end_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="applicable_to">Applicable To</label>
    <select name="applicable_to" id="applicable_to" class="form-control @error('applicable_to') is-invalid @enderror">
        <option value="all" {{ old('applicable_to', $discount->applicable_to ?? '') === 'all' ? 'selected' : '' }}>All</option>
        <option value="client_agent" {{ old('applicable_to', $discount->applicable_to ?? '') === 'client_agent' ? 'selected' : '' }}>Client/Agent</option>
        <option value="local" {{ old('applicable_to', $discount->applicable_to ?? '') === 'local' ? 'selected' : '' }}>Local</option>
    </select>
    @error('applicable_to')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group form-check">
    <input type="hidden" name="status" value="0">
    <input type="checkbox" name="status" id="status" class="form-check-input" value="1"
        {{ old('status', $discount->status ?? true) ? 'checked' : '' }}>
    <label for="status" class="form-check-label">Active</label>
</div>
