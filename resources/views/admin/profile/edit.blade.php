@extends('layouts.admin')

@section('content_title', 'My profile')

@section('content_body')
    @php
        // Land on the Password tab after a password change or a failed one.
        $passwordTab = session('password_tab')
            || $errors->hasAny(['current_password', 'new_password']);
    @endphp

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    @if ($user->avatar)
                        <img src="{{ media_url($user->avatar, thumb: true) }}" alt="{{ $user->name }}"
                             class="mb-3"
                             style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover;">
                    @else
                        <span class="brand-mark mb-3"
                              style="width: 96px; height: 96px; flex: none; border-radius: 50%; font-size: 1.9rem; margin: 0 auto 1rem;">
                            {{ Str::upper(Str::substr($user->name, 0, 2)) }}
                        </span>
                    @endif

                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <div>
                        @if ($user->is_admin)
                            <span class="badge badge-soft-primary">Administrator</span>
                        @endif
                        @foreach ($user->roles as $role)
                            <span class="badge badge-soft-muted">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer">
                    <dl class="detail-list mb-0">
                        <dt>Employee ID</dt>
                        <dd>{{ $user->employee_id ?? '—' }}</dd>
                        <dt>Department</dt>
                        <dd>{{ $user->department?->name ?? '—' }}</dd>
                        <dt>Member since</dt>
                        <dd class="mb-0">{{ $user->created_at?->format('d M Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs w-100 px-2 pt-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $passwordTab ? '' : 'active' }}" href="#tab-details" data-toggle="tab">
                                <i class="fas fa-user mr-1"></i> Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $passwordTab ? 'active' : '' }}" href="#tab-password" data-toggle="tab">
                                <i class="fas fa-lock mr-1"></i> Password
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">
                        {{-- ------------------------------------------- details --}}
                        <div class="tab-pane fade {{ $passwordTab ? '' : 'show active' }}" id="tab-details">
                            <form action="{{ route('admin.profile.update') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Full name</label>
                                            <input type="text" name="name" id="name" required maxlength="255"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name', $user->name) }}">
                                            @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" id="email" required maxlength="255"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $user->email) }}">
                                            @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">
                                                You sign in with this address.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" maxlength="50"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" rows="2" maxlength="500"
                                        class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="avatar">Profile photo</label>
                                    <input type="file" name="avatar" id="avatar"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        class="form-control-file">
                                    @error('avatar')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror

                                    @if ($user->avatar)
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="checkbox" name="remove_avatar" value="1"
                                                id="remove_avatar" class="custom-control-input">
                                            <label for="remove_avatar" class="custom-control-label">
                                                Remove my current photo
                                            </label>
                                        </div>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Save changes
                                </button>
                            </form>
                        </div>

                        {{-- ------------------------------------------ password --}}
                        <div class="tab-pane fade {{ $passwordTab ? 'show active' : '' }}" id="tab-password">
                            <form action="{{ route('admin.profile.password') }}" method="post">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label for="current_password">Current password</label>
                                    <input type="password" name="current_password" id="current_password" required
                                        autocomplete="current-password"
                                        class="form-control @error('current_password') is-invalid @enderror">
                                    @error('current_password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="new_password">New password</label>
                                            <input type="password" name="new_password" id="new_password" required
                                                autocomplete="new-password"
                                                class="form-control @error('new_password') is-invalid @enderror">
                                            @error('new_password')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="new_password_confirmation">Confirm new password</label>
                                            <input type="password" name="new_password_confirmation"
                                                id="new_password_confirmation" required autocomplete="new-password"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key mr-1"></i> Change password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
