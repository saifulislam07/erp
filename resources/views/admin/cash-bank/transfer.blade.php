@extends('layouts.admin')

@section('content_title', 'Transfer Cash / Bank')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transfer</h3>
        </div>

        <div class="card-body">
            <p>Cash Balance: <strong>{{ money($cashBalance) }}</strong> | Bank Balance: <strong>{{ money($bankBalance) }}</strong></p>
        </div>

        <form action="{{ route('admin.cash-bank.transfer') }}" method="post">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="from_method">From</label>
                    <select name="from_method" id="from_method" class="form-control @error('from_method') is-invalid @enderror">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                    </select>
                    @error('from_method')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="to_method">To</label>
                    <select name="to_method" id="to_method" class="form-control @error('to_method') is-invalid @enderror">
                        <option value="bank">Bank</option>
                        <option value="cash">Cash</option>
                    </select>
                    @error('to_method')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}">
                    @error('amount')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="note">Note</label>
                    <textarea name="note" id="note" rows="2" class="form-control">{{ old('note') }}</textarea>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Transfer</button>
                <a href="{{ route('admin.cash-bank.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
