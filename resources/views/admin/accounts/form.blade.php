@php $account = $account ?? null; @endphp

<div class="form-group">
    <label for="type">Type</label>
    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
        <option value="payable" {{ old('type', $account->type ?? $type ?? '') === 'payable' ? 'selected' : '' }}>Payable</option>
        <option value="receivable" {{ old('type', $account->type ?? $type ?? '') === 'receivable' ? 'selected' : '' }}>Receivable</option>
    </select>
    @error('type')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="party_type">Party Type</label>
    <select name="party_type" id="party_type" class="form-control @error('party_type') is-invalid @enderror">
        <option value="supplier" {{ old('party_type', $account->party_type ?? '') === 'supplier' ? 'selected' : '' }}>Supplier</option>
        <option value="client" {{ old('party_type', $account->party_type ?? '') === 'client' ? 'selected' : '' }}>Client</option>
    </select>
    @error('party_type')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="party_id_supplier">Supplier</label>
    <select name="party_id" id="party_id_supplier" class="party-select form-control" data-type="supplier">
        <option value="">-- Select Supplier --</option>
        @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id }}" {{ (int) old('party_id', $account->party_id ?? '') === $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="party_id_client">Client</label>
    <select name="party_id" id="party_id_client" class="party-select form-control" data-type="client">
        <option value="">-- Select Client --</option>
        @foreach ($clients as $client)
            <option value="{{ $client->id }}" {{ (int) old('party_id', $account->party_id ?? '') === $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
        @endforeach
    </select>
    @error('party_id')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="amount">Amount</label>
    <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $account->amount ?? '') }}">
    @error('amount')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="due_date">Due Date</label>
    <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', optional($account?->due_date)->format('Y-m-d')) }}">
    @error('due_date')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $account->description ?? '') }}</textarea>
</div>

<script>
    (function () {
        function toggleParty() {
            var type = document.getElementById('party_type').value;
            document.querySelectorAll('.party-select').forEach(function (el) {
                var wrapper = el.closest('.form-group');
                wrapper.style.display = el.dataset.type === type ? '' : 'none';
                if (el.dataset.type !== type) el.value = '';
            });
        }
        document.getElementById('party_type').addEventListener('change', toggleParty);
        toggleParty();
    })();
</script>
