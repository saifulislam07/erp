@extends('layouts.admin')

@section('content_title', 'Pay Salary')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pay Salary</h3>
        </div>

        <form action="{{ route('admin.salaries.store') }}" method="post">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="user_id">Employee</label>
                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror">
                        <option value="">-- Select Employee --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ (int) old('user_id') === $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->employee_id }})</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="month">Month</label>
                    <input type="month" name="month" id="month" class="form-control @error('month') is-invalid @enderror" value="{{ old('month', now()->format('Y-m')) }}">
                    @error('month')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="basic_salary">Basic Salary</label>
                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary') }}">
                            @error('basic_salary')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="deduction">Deduction</label>
                            <input type="number" step="0.01" name="deduction" id="deduction" class="form-control @error('deduction') is-invalid @enderror" value="{{ old('deduction', 0) }}">
                            @error('deduction')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                    </select>
                    @error('payment_method')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="note">Note</label>
                    <textarea name="note" id="note" rows="2" class="form-control">{{ old('note') }}</textarea>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Pay Salary</button>
                <a href="{{ route('admin.salaries.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
