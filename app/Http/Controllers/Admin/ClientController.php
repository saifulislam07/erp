<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientRequest;
use App\Models\Client;
use App\Models\OrderReturn;
use App\Models\Sale;
use App\Notifications\ClientPasswordResetNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData();
        }

        return view('admin.clients.index');
    }

    /**
     * Server-side DataTables feed for the client/agent listing.
     */
    protected function indexData(): JsonResponse
    {
        return DataTables::eloquent(Client::query())
            ->addIndexColumn()
            ->addColumn('type_badge', fn (Client $client) => view('admin.clients.partials.type-cell', compact('client'))->render())
            ->addColumn('phone_number', fn (Client $client) => e($client->phone ?: '-'))
            ->addColumn('state', fn (Client $client) => view('admin.clients.partials.status-cell', compact('client'))->render())
            ->addColumn('actions', fn (Client $client) => view('admin.clients.partials.actions', compact('client'))->render())
            ->orderColumn('type_badge', 'type $1')
            ->orderColumn('phone_number', 'phone $1')
            ->orderColumn('state', 'status $1')
            ->filterColumn('phone_number', fn ($query, $keyword) => $query->where('phone', 'like', "%{$keyword}%"))
            ->rawColumns(['type_badge', 'state', 'actions'])
            ->toJson();
    }

    /**
     * Kept as an alias so existing AJAX callers of /admin/clients/search keep
     * working. The canonical implementation lives in SearchController.
     */
    public function search(Request $request, SearchController $search): JsonResponse
    {
        return $search->clients($request);
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

    public function show(Client $client): View
    {
        $user = auth()->user();

        // Each panel is scoped to the permission guarding the module it draws
        // from, so a client-only role never sees order/sale/return records.
        $orderStats = ['total' => 0, 'pending' => 0, 'delivered' => 0, 'cancelled' => 0, 'value' => 0.0];
        $recentOrders = collect();
        $recentSales = collect();
        $returns = collect();

        if ($user->can('order.view')) {
            $orderStats = [
                'total' => $client->orders()->count(),
                'pending' => $client->orders()->whereIn('status', ['pending', 'processing', 'confirmed'])->count(),
                'delivered' => $client->orders()->where('status', 'delivered')->count(),
                'cancelled' => $client->orders()->whereIn('status', ['cancelled', 'rejected'])->count(),
                // Cancelled/rejected orders never earn revenue, so keep them out of the total.
                'value' => (float) $client->orders()->whereNotIn('status', ['cancelled', 'rejected'])->sum('total_amount'),
            ];

            $recentOrders = $client->orders()->latest()->take(10)->get();
        }

        if ($user->can('sale.view')) {
            $recentSales = Sale::where('customer_type', 'client_agent')
                ->where('customer_id', $client->id)
                ->latest()
                ->take(10)
                ->get();
        }

        if ($user->can('return.view')) {
            $returns = OrderReturn::with('returnType')
                ->where('client_id', $client->id)
                ->latest()
                ->take(10)
                ->get();
        }

        return view('admin.clients.show', compact('client', 'orderStats', 'recentOrders', 'recentSales', 'returns'));
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
