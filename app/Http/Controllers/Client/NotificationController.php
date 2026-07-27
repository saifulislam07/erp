<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $client = $request->user('client');

        $notifications = $client->notifications()->paginate(20);

        $client->unreadNotifications->each->markAsRead();

        return view('client.notifications.index', compact('notifications'));
    }

    public function poll(Request $request): JsonResponse
    {
        $client = $request->user('client');

        $notifications = $client->notifications()->latest()->limit(5)->get();

        return response()->json([
            'label' => $client->unreadNotifications()->count(),
            'label_color' => 'danger',
            'dropdown' => view('client.notifications.partials.dropdown', compact('notifications'))->render(),
        ]);
    }
}
