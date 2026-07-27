@extends('auth.layout', [
    'heading' => 'Choose a new password',
    'subheading' => 'Pick something you have not used here before.',
])

@section('form')
    <form action="{{ route('password.store') }}" method="post">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <label for="email">Email address</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" name="email" id="email" required autocomplete="username"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $request->email) }}">
            </div>
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">New password</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                </div>
                <input type="password" name="password" id="password" required autofocus autocomplete="new-password"
                    class="form-control @error('password') is-invalid @enderror">
            </div>
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm new password</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                </div>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    required autocomplete="new-password"
                    class="form-control @error('password_confirmation') is-invalid @enderror">
            </div>
            @error('password_confirmation')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-key mr-1"></i> Set new password
        </button>
    </form>

    <p class="auth-links">
        <a href="{{ route('login') }}">Back to sign in</a>
    </p>
@endsection
