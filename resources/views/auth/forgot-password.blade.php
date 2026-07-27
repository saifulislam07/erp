@extends('auth.layout', [
    'heading' => 'Reset your password',
    'subheading' => 'Enter your email address and we will send you a link to set a new password.',
])

@section('form')
    <form action="{{ route('password.email') }}" method="post">
        @csrf

        <div class="form-group">
            <label for="email">Email address</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" name="email" id="email" required autofocus
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="you@company.com">
            </div>
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane mr-1"></i> Send reset link
        </button>
    </form>

    <p class="auth-links">
        <a href="{{ route('login') }}">Back to sign in</a>
    </p>
@endsection
