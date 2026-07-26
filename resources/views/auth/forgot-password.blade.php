@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', 'Forgot Password')

@section('auth_body')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <p class="text-muted">
        Enter your email address and we will email you a password reset link.
    </p>

    <form action="{{ route('password.email') }}" method="post">
        @csrf

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="Email" autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-block btn-flat btn-primary">
            <span class="fas fa-share-square"></span>
            Send Password Reset Link
        </button>
    </form>
@stop

@section('auth_footer')
    <p class="my-0">
        <a href="{{ route('login') }}">Back to login</a>
    </p>
@stop
