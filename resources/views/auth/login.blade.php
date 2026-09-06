@extends('auth.layout', [
    'heading' => 'Sign in',
    'subheading' => 'Use the account your administrator set up for you.',
])

@php
    // Local development only: prefill the seeded admin account so it does not
    // have to be retyped. `environment('local')` is the gate, so this is inert
    // on staging and production regardless of what config/erp.php holds.
    $devLogin = app()->environment('local') ? config('erp.dev_login') : null;
@endphp

@section('form')
    @if ($devLogin)
        <div class="auth-dev-note">
            <i class="fas fa-flask mr-1"></i>
            Local environment — admin credentials prefilled from
            <code>config/erp.dev_login</code>.
        </div>
    @endif

    <form action="{{ route('login') }}" method="post">
        @csrf

        <div class="form-group">
            <label for="email">Email address</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" name="email" id="email" required autofocus autocomplete="username"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $devLogin['email'] ?? '') }}" placeholder="you@company.com">
            </div>
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                </div>
                <input type="password" name="password" id="password" required autocomplete="current-password"
                    class="form-control @error('password') is-invalid @enderror"
                    value="{{ $devLogin['password'] ?? '' }}"
                    placeholder="Your password">
                <div class="input-group-append">
                    <button type="button" class="btn btn-secondary" id="toggle-password"
                            aria-label="Show password" title="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="custom-control custom-checkbox mb-4">
            <input type="checkbox" name="remember" id="remember" value="1"
                class="custom-control-input" @checked(old('remember'))>
            <label for="remember" class="custom-control-label">Keep me signed in</label>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt mr-1"></i> Sign in
        </button>
    </form>

    @if (Route::has('password.request'))
        <p class="auth-links">
            <a href="{{ route('password.request') }}">Forgot your password?</a>
        </p>
    @endif
@endsection

@push('js')
    <script>
        $(function () {
            $('#toggle-password').on('click', function () {
                const $field = $('#password');
                const showing = $field.attr('type') === 'text';
                const label = showing ? 'Show password' : 'Hide password';

                $field.attr('type', showing ? 'password' : 'text');
                $(this)
                    .attr({ 'aria-label': label, title: label })
                    .find('i').attr('class', showing ? 'fas fa-eye' : 'fas fa-eye-slash');
            });
        });
    </script>
@endpush
