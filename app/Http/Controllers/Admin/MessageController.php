<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $clientIds = Message::where('sender_type', 'client')->pluck('sender_id')
            ->merge(Message::where('receiver_type', 'client')->pluck('receiver_id'))
            ->unique();

        $conversations = Client::whereIn('id', $clientIds)->get()->map(function (Client $client) {
            $client->last_message = Message::forClient($client->id)->latest()->first();
            $client->unread_count = Message::where('sender_type', 'client')
                ->where('sender_id', $client->id)
                ->where('is_read', false)
                ->count();

            return $client;
        })->sortByDesc(fn (Client $client) => $client->last_message?->created_at)->values();

        return view('admin.messages.index', compact('conversations'));
    }

    public function show(Request $request, Client $client): View|JsonResponse
    {
        Message::where('sender_type', 'client')
            ->where('sender_id', $client->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::forClient($client->id)->oldest()->get();

        if ($request->wantsJson()) {
            return response()->json(['messages' => $messages]);
        }

        return view('admin.messages.show', compact('client', 'messages'));
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string']]);

        Message::create([
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'receiver_type' => 'client',
            'receiver_id' => $client->id,
            'message' => $request->message,
        ]);

        return redirect()->route('admin.messages.show', $client);
    }
}
