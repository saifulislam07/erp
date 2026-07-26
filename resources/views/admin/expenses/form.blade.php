@php $expense = $expense ?? null; @endphp

<div class="form-group">
    <label for="expense_head_id">Expense Head</label>
    <select name="expense_head_id" id="expense_head_id" class="form-control @error('expense_head_id') is-invalid @enderror">
        <option value="">-- Select Head --</option>
        @foreach ($expenseHeads as $head)
            <option value="{{ $head->id }}" {{ (int) old('expense_head_id', $expense->expense_head_id ?? '') === $head->id ? 'selected' : '' }}>{{ $head->name }}</option>
        @endforeach
    </select>
    @error('expense_head_id')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount ?? '') }}">
            @error('amount')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="expense_date">Expense Date</label>
            <input type="date" name="expense_date" id="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', optional($expense?->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
            @error('expense_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="payment_method">Payment Method</label>
    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
        <option value="cash" {{ old('payment_method', $expense->payment_method ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
        <option value="bank" {{ old('payment_method', $expense->payment_method ?? '') === 'bank' ? 'selected' : '' }}>Bank</option>
    </select>
    @error('payment_method')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $expense->description ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="receipt_file">Receipt File</label>
    <input type="file" name="receipt_file" id="receipt_file" class="form-control-file @error('receipt_file') is-invalid @enderror">
    @error('receipt_file')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
    @if (!empty($expense?->receipt_file))
        <div class="mt-1"><a href="{{ asset('storage/'.$expense->receipt_file) }}" target="_blank">View current receipt</a></div>
    @endif
</div>
