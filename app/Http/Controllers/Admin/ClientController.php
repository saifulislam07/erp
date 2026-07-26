<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientRequest;
use App\Models\Client;
use App\Notifications\ClientPasswordResetNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::latest()->get();

        return view('admin.clients.index', compact('clients'));
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $clients = Client::where('status', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('unique_id', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'unique_id', 'name', 'phone', 'type']);

        return response()->json($clients);
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'business_name' => $request->business_name,
            'type' => $request->type,
            'password' => $request->password,
            'status' => true,
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $client->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'business_name' => $request->business_name,
            'type' => $request->type,
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
    }

    public function resetPassword(Client $client): RedirectResponse
    {
        $newPassword = Str::random(10);

        $client->update(['password' => $newPassword]);

        $client->notify(new ClientPasswordResetNotification($newPassword));

        return redirect()->route('admin.clients.index')->with('success', 'Password reset and emailed to the client.');
    }
}
