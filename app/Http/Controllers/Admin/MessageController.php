<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Message;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
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

        $activeClient = null;
        $messages = collect();

        if ($request->filled('client')) {
            $activeClient = Client::find($request->integer('client'));
        }

        if ($activeClient) {
            Message::where('sender_type', 'client')
                ->where('sender_id', $activeClient->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = Message::forClient($activeClient->id)->oldest()->get();
        }

        return view('admin.messages.index', compact('conversations', 'activeClient', 'messages'));
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

        return redirect()->route('admin.messages.index', ['client' => $client->id]);
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string']]);

        $message = Message::create([
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'receiver_type' => 'client',
            'receiver_id' => $client->id,
            'message' => $request->message,
        ]);

        $client->notify(new MessageReceivedNotification($message, 'Support'));

        return redirect()->route('admin.messages.index', ['client' => $client->id]);
    }

    public function resolve(Client $client): RedirectResponse
    {
        $client->update(['conversation_resolved' => ! $client->conversation_resolved]);

        return redirect()->route('admin.messages.index', ['client' => $client->id]);
    }
}
