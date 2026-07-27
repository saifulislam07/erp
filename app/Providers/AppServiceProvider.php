<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super admins bypass every ability. This mirrors the `is_admin` short
        // circuit already present in every policy and in the AdminOnly /
        // CheckPermission middleware, so sidebar `can` filters stay consistent
        // with route authorization even if an admin has no role assigned.
        // `view-home-fallback` is excluded: it is a negative ability that
        // admins must NOT hold, and Gate::before would otherwise grant it.
        Gate::before(function (User $user, string $ability) {
            if ($ability === 'view-home-fallback') {
                return null;
            }

            return $user->is_admin ? true : null;
        });

        // Admin-only areas that are not backed by a Spatie permission
        // (activity log, system settings). Used to gate the sidebar so the
        // items are hidden rather than 403-ing on click.
        Gate::define('access-system-area', fn (User $user) => (bool) $user->is_admin);

        // The fallback home page replaces the dashboard in the sidebar for
        // users who cannot open the dashboard, so exactly one landing item
        // is ever shown.
        Gate::define('view-home-fallback', function (User $user) {
            if ($user->is_admin) {
                return false;
            }

            return ! $user->getAllPermissions()->contains('name', 'dashboard.view');
        });
    }
}
