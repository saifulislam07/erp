<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $client = $request->user('client');

        Message::where('receiver_type', 'client')
            ->where('receiver_id', $client->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::forClient($client->id)->oldest()->get();

        if ($request->wantsJson()) {
            return response()->json(['messages' => $messages]);
        }

        return view('client.messages.index', compact('messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string']]);

        Message::create([
            'sender_type' => 'client',
            'sender_id' => $request->user('client')->id,
            'receiver_type' => 'admin',
            'receiver_id' => 0,
            'message' => $request->message,
        ]);

        return redirect()->route('client.messages.index');
    }
}
