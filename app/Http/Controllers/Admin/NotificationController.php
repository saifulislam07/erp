<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()->paginate(20);

        $user->unreadNotifications->each->markAsRead();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()->latest()->limit(5)->get();

        return response()->json([
            'label' => $user->unreadNotifications()->count(),
            'label_color' => 'danger',
            'dropdown' => view('admin.notifications.partials.dropdown', compact('notifications'))->render(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $model = $request->user()->notifications()->findOrFail($notification);
        $model->markAsRead();

        return back();
    }
}
