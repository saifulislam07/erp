<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminLanding;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Neutral landing page for users without `dashboard.view`. Carries no
     * business figures of its own — it only links onward to the modules the
     * signed-in user is allowed to open.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        // Users who do have dashboard access belong on the dashboard; keep a
        // single canonical landing page per user instead of two similar ones.
        if ($user->can('dashboard.view')) {
            return redirect()->route('admin.dashboard');
        }

        $modules = AdminLanding::accessibleModules($user);

        return view('admin.home', compact('user', 'modules'));
    }
}
