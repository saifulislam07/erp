@extends('layouts.admin')

@section('content_title', 'Settings')

@section('content_body')
    @php
        // A failed or successful test send should land the user back on the
        // Email tab rather than on Company.
        $tab = session('mail_tab') ? 'mail' : 'company';
    @endphp

    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs w-100 px-2 pt-2" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'company' ? 'active' : '' }}" href="#tab-company" data-toggle="tab">
                        <i class="fas fa-building mr-1"></i> Company
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#tab-operations" data-toggle="tab">
                        <i class="fas fa-sliders-h mr-1"></i> Operations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'mail' ? 'active' : '' }}" href="#tab-mail" data-toggle="tab">
                        <i class="fas fa-envelope mr-1"></i> Email
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="tab-content">
            {{-- ------------------------------------------------------ company --}}
            <div class="tab-pane fade {{ $tab === 'company' ? 'show active' : '' }}" id="tab-company">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Company details</h3>
                            </div>

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="company_name">Company name</label>
                                    <input type="text" name="company_name" id="company_name" required maxlength="255"
                                        class="form-control @error('company_name') is-invalid @enderror"
                                        value="{{ old('company_name', $settings['company_name']) }}">
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Shown in the sidebar, the browser tab and on every invoice.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="company_address">Address</label>
                                    <textarea name="company_address" id="company_address" rows="2"
                                        class="form-control @error('company_address') is-invalid @enderror">{{ old('company_address', $settings['company_address']) }}</textarea>
                                    @error('company_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_phone">Phone</label>
                                            <input type="text" name="company_phone" id="company_phone" maxlength="50"
                                                class="form-control @error('company_phone') is-invalid @enderror"
                                                value="{{ old('company_phone', $settings['company_phone']) }}">
                                            @error('company_phone')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_email">Email</label>
                                            <input type="email" name="company_email" id="company_email" maxlength="255"
                                                class="form-control @error('company_email') is-invalid @enderror"
                                                value="{{ old('company_email', $settings['company_email']) }}">
                                            @error('company_email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="vat_registration_number">VAT registration number</label>
                                    <input type="text" name="vat_registration_number" id="vat_registration_number" maxlength="100"
                                        class="form-control @error('vat_registration_number') is-invalid @enderror"
                                        value="{{ old('vat_registration_number', $settings['vat_registration_number']) }}">
                                    @error('vat_registration_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Printed in the invoice header when set.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Logo</h3>
                            </div>

                            <div class="card-body text-center">
                                @if ($companyLogo)
                                    <img src="{{ media_url($companyLogo) }}" alt="Company logo"
                                         style="max-height: 90px; max-width: 100%;" class="mb-3">
                                @else
                                    <div class="empty-state pt-0">
                                        <i class="fas fa-image"></i>
                                        <p>No logo uploaded — the sidebar shows a monogram instead.</p>
                                    </div>
                                @endif

                                <div class="form-group text-left">
                                    <label for="company_logo">Upload a new logo</label>
                                    <input type="file" name="company_logo" id="company_logo"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        class="form-control-file">
                                    @error('company_logo')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        A wide, transparent PNG works best. Converted to WebP on upload.
                                    </small>
                                </div>

                                @if ($companyLogo)
                                    <div class="custom-control custom-checkbox text-left">
                                        <input type="checkbox" name="remove_company_logo" value="1"
                                            id="remove_company_logo" class="custom-control-input">
                                        <label for="remove_company_logo" class="custom-control-label">
                                            Remove the current logo
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- --------------------------------------------------- operations --}}
            <div class="tab-pane fade" id="tab-operations">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Operational defaults</h3>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currency_symbol">Currency symbol</label>
                                            <input type="text" name="currency_symbol" id="currency_symbol" required maxlength="10"
                                                class="form-control @error('currency_symbol') is-invalid @enderror"
                                                value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '৳') }}">
                                            @error('currency_symbol')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Prefixed to every amount shown in the panel.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="low_stock_threshold_default">Default low-stock level</label>
                                            <input type="number" min="0" name="low_stock_threshold_default"
                                                id="low_stock_threshold_default" required
                                                class="form-control @error('low_stock_threshold_default') is-invalid @enderror"
                                                value="{{ old('low_stock_threshold_default', $settings['low_stock_threshold_default'] ?? 10) }}">
                                            @error('low_stock_threshold_default')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Used for products that do not set their own level.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="invoice_footer_note">Invoice footer note</label>
                                    <textarea name="invoice_footer_note" id="invoice_footer_note" rows="2" maxlength="500"
                                        class="form-control @error('invoice_footer_note') is-invalid @enderror"
                                        placeholder="Payment terms, bank details, return policy…">{{ old('invoice_footer_note', $settings['invoice_footer_note']) }}</textarea>
                                    @error('invoice_footer_note')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- --------------------------------------------------------- mail --}}
            <div class="tab-pane fade {{ $tab === 'mail' ? 'show active' : '' }}" id="tab-mail">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Outgoing mail server</h3>
                            </div>

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="mail_mailer">Transport</label>
                                    <select name="mail_mailer" id="mail_mailer" class="form-control">
                                        @foreach (['smtp' => 'SMTP server', 'log' => 'Write to log file (nothing is sent)', 'array' => 'Discard (testing only)'] as $value => $label)
                                            <option value="{{ $value }}"
                                                @selected(old('mail_mailer', $settings['mail_mailer'] ?? config('mail.default')) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Choose SMTP in production. “Log” is useful while setting the system up.
                                    </small>
                                </div>

                                <div id="smtp-fields">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="mail_host">Host</label>
                                                <input type="text" name="mail_host" id="mail_host" maxlength="255"
                                                    class="form-control @error('mail_host') is-invalid @enderror"
                                                    value="{{ old('mail_host', $settings['mail_host']) }}"
                                                    placeholder="smtp.example.com">
                                                @error('mail_host')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mail_port">Port</label>
                                                <input type="number" name="mail_port" id="mail_port" min="1" max="65535"
                                                    class="form-control @error('mail_port') is-invalid @enderror"
                                                    value="{{ old('mail_port', $settings['mail_port']) }}"
                                                    placeholder="587">
                                                @error('mail_port')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mail_username">Username</label>
                                                <input type="text" name="mail_username" id="mail_username" maxlength="255"
                                                    autocomplete="off"
                                                    class="form-control @error('mail_username') is-invalid @enderror"
                                                    value="{{ old('mail_username', $settings['mail_username']) }}">
                                                @error('mail_username')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mail_password">Password</label>
                                                <input type="password" name="mail_password" id="mail_password"
                                                    autocomplete="new-password"
                                                    class="form-control @error('mail_password') is-invalid @enderror"
                                                    placeholder="{{ $hasMailPassword ? 'Saved — leave blank to keep it' : 'Not set' }}">
                                                @error('mail_password')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                                @if ($hasMailPassword)
                                                    <div class="custom-control custom-checkbox mt-2">
                                                        <input type="checkbox" name="clear_mail_password" value="1"
                                                            id="clear_mail_password" class="custom-control-input">
                                                        <label for="clear_mail_password" class="custom-control-label">
                                                            Clear the saved password
                                                        </label>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="mail_encryption">Encryption</label>
                                        <select name="mail_encryption" id="mail_encryption" class="form-control">
                                            @foreach (['tls' => 'TLS (usually port 587)', 'ssl' => 'SSL (usually port 465)', 'none' => 'None'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    @selected(old('mail_encryption', $settings['mail_encryption'] ?? 'tls') === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="mail_from_address">Send from address</label>
                                            <input type="email" name="mail_from_address" id="mail_from_address" maxlength="255"
                                                class="form-control @error('mail_from_address') is-invalid @enderror"
                                                value="{{ old('mail_from_address', $settings['mail_from_address']) }}">
                                            @error('mail_from_address')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="mail_from_name">Send from name</label>
                                            <input type="text" name="mail_from_name" id="mail_from_name" maxlength="255"
                                                class="form-control @error('mail_from_name') is-invalid @enderror"
                                                value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                                                placeholder="{{ $settings['company_name'] }}">
                                            @error('mail_from_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Automatic emails</h3>
                            </div>

                            <div class="card-body">
                                @foreach ([
                                    'mail_send_sale_invoice' => ['Invoice on sale', 'Email the invoice to the customer when a sale is recorded.'],
                                    'mail_send_order_updates' => ['Order updates', 'Tell clients when their order is accepted, packed or delivered.'],
                                    'mail_send_password_reset' => ['Password resets', 'Send the reset link when someone forgets their password.'],
                                ] as $key => $copy)
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="hidden" name="{{ $key }}" value="0">
                                        <input type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1"
                                            class="custom-control-input"
                                            @checked(old($key, $settings[$key] ?? '1'))>
                                        <label for="{{ $key }}" class="custom-control-label">
                                            {{ $copy[0] }}
                                            <small class="d-block text-muted">{{ $copy[1] }}</small>
                                        </label>
                                    </div>
                                @endforeach

                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Emails are queued. A worker (<code>php artisan queue:work</code>) must be
                                    running for them to actually leave the server.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body page-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Save settings
                </button>
            </div>
        </div>
    </form>

    {{--
        Separate form: the test send must use the details already saved, so it
        cannot be nested inside the settings form.
    --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Test the mail configuration</h3>
        </div>

        <form action="{{ route('admin.settings.test-mail') }}" method="post">
            @csrf
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="test_email">Send a test message to</label>
                            <input type="email" name="test_email" id="test_email" required
                                class="form-control @error('test_email') is-invalid @enderror"
                                value="{{ old('test_email', auth()->user()->email) }}">
                            @error('test_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-paper-plane mr-1"></i> Send test email
                        </button>
                        <small class="form-text text-muted">
                            Uses the settings currently saved — save first, then test.
                        </small>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            // Host, port and credentials only mean anything for the SMTP
            // transport; hide them otherwise so the form is not misleading.
            function toggleSmtpFields() {
                $('#smtp-fields').toggle($('#mail_mailer').val() === 'smtp');
            }

            $('#mail_mailer').on('change', toggleSmtpFields);
            toggleSmtpFields();
        });
    </script>
@endpush
