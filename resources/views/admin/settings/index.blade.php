@extends('layouts.admin')

@section('content_title', 'Settings')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Company & System Settings</h3>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Company Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" class="form-control @error('company_name') is-invalid @enderror">
                        @error('company_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Company Address</label>
                    <div class="col-sm-9">
                        <textarea name="company_address" class="form-control @error('company_address') is-invalid @enderror">{{ old('company_address', $settings['company_address']) }}</textarea>
                        @error('company_address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Company Phone</label>
                    <div class="col-sm-9">
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}" class="form-control @error('company_phone') is-invalid @enderror">
                        @error('company_phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Company Email</label>
                    <div class="col-sm-9">
                        <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}" class="form-control @error('company_email') is-invalid @enderror">
                        @error('company_email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Company Logo</label>
                    <div class="col-sm-9">
                        @if ($companyLogo)
                            <div class="mb-2"><img src="{{ asset('storage/'.$companyLogo) }}" alt="Company Logo" style="max-height: 60px;"></div>
                        @endif
                        <input type="file" name="company_logo" class="form-control-file @error('company_logo') is-invalid @enderror">
                        @error('company_logo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Currency Symbol</label>
                    <div class="col-sm-9">
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '৳') }}" class="form-control @error('currency_symbol') is-invalid @enderror">
                        @error('currency_symbol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Default Low Stock Threshold</label>
                    <div class="col-sm-9">
                        <input type="number" name="low_stock_threshold_default" value="{{ old('low_stock_threshold_default', $settings['low_stock_threshold_default'] ?? 10) }}" class="form-control @error('low_stock_threshold_default') is-invalid @enderror">
                        @error('low_stock_threshold_default') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">VAT Registration Number</label>
                    <div class="col-sm-9">
                        <input type="text" name="vat_registration_number" value="{{ old('vat_registration_number', $settings['vat_registration_number']) }}" class="form-control @error('vat_registration_number') is-invalid @enderror">
                        @error('vat_registration_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
@endsection
