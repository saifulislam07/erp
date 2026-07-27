@php
    $role = $role ?? null;
    $assignedPermissions = $assignedPermissions ?? [];
@endphp

<div class="form-group">
    <label for="name">Role Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $role->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="d-block">
        Menu Access (Permissions)
        <button type="button" id="select-all-permissions" class="btn btn-link btn-sm p-0 ml-2">Select All</button>
        <button type="button" id="clear-all-permissions" class="btn btn-link btn-sm p-0 ml-2">Clear All</button>
    </label>

    <div class="row">
        @foreach ($groupedPermissions as $moduleKey => $module)
            @php
                $modulePermissionNames = $module['permissions']->pluck('name')->all();
                $oldSelected = old('permissions', $assignedPermissions);
            @endphp
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card card-outline card-secondary h-100 mb-0">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold">
                            <input type="checkbox" class="module-toggle" data-module="{{ $moduleKey }}">
                            {{ $module['label'] }}
                        </h3>
                    </div>
                    <div class="card-body py-2">
                        @foreach ($module['permissions'] as $permission)
                            <div class="form-check">
                                <input type="checkbox" name="permissions[]" id="permission-{{ $permission->id }}"
                                    value="{{ $permission->name }}"
                                    class="form-check-input module-{{ $moduleKey }}-permission"
                                    {{ in_array($permission->name, $oldSelected) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission-{{ $permission->id }}">
                                    {{ \Illuminate\Support\Str::after($permission->name, '.') }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @error('permissions')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

@push('js')
    <script>
        $(function () {
            function syncModuleToggle(moduleKey) {
                const boxes = $('.module-' + moduleKey + '-permission');
                const allChecked = boxes.length > 0 && boxes.length === boxes.filter(':checked').length;
                $('.module-toggle[data-module="' + moduleKey + '"]').prop('checked', allChecked);
            }

            $('[class*="-permission"]').each(function () {
                const moduleKey = this.className.match(/module-(\S+)-permission/)[1];
                syncModuleToggle(moduleKey);
            });

            $('.module-toggle').on('change', function () {
                const moduleKey = $(this).data('module');
                $('.module-' + moduleKey + '-permission').prop('checked', this.checked);
            });

            $('[class*="-permission"]').on('change', function () {
                const moduleKey = this.className.match(/module-(\S+)-permission/)[1];
                syncModuleToggle(moduleKey);
            });

            $('#select-all-permissions').on('click', function () {
                $('input[name="permissions[]"], .module-toggle').prop('checked', true);
            });

            $('#clear-all-permissions').on('click', function () {
                $('input[name="permissions[]"], .module-toggle').prop('checked', false);
            });
        });
    </script>
@endpush
