@extends('layouts.admin')

@section('content_title', 'Home')

@section('content_body')
    <div class="card">
        <div class="card-body">
            <h4 class="mb-1">Welcome back, {{ $user->name }}</h4>
            <p class="text-muted mb-0">
                @if ($user->roles->isNotEmpty())
                    Role: {{ $user->roles->pluck('name')->join(', ') }}
                @endif
                @if ($user->employee_id)
                    &middot; Employee ID: {{ $user->employee_id }}
                @endif
                @if ($user->department)
                    &middot; {{ $user->department->name }}
                @endif
            </p>
        </div>
    </div>

    @if (empty($modules))
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                <h5>No modules assigned yet</h5>
                <p class="text-muted mb-0">
                    Your account does not have access to any module right now.
                    Please contact an administrator to get the required permissions.
                </p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Your Modules</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($modules as $module)
                        <div class="col-lg-3 col-sm-6">
                            <a href="{{ route($module['route']) }}" class="text-decoration-none">
                                <div class="small-box bg-{{ $module['color'] }}">
                                    <div class="inner">
                                        <h5 class="mb-0">{{ $module['label'] }}</h5>
                                        <p class="mb-0 text-sm">Open</p>
                                    </div>
                                    <div class="icon"><i class="{{ $module['icon'] }}"></i></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <a href="{{ route('admin.password.edit') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-lock mr-1"></i> Change Password
            </a>
        </div>
    </div>
@endsection
