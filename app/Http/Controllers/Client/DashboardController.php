<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $client = $request->user('client');

        $widgets = [
            'pending_orders' => Schema::hasTable('orders')
                ? DB::table('orders')->where('client_id', $client->id)->where('status', 'pending')->count()
                : 0,
            'orders_in_delivery' => Schema::hasTable('orders')
                ? DB::table('orders')->where('client_id', $client->id)->where('status', 'on_delivery')->count()
                : 0,
            'unread_messages' => Message::where('receiver_type', 'client')
                ->where('receiver_id', $client->id)
                ->where('is_read', false)
                ->count(),
        ];

        $recentOrders = Schema::hasTable('orders')
            ? DB::table('orders')->where('client_id', $client->id)->latest()->limit(5)->get()
            : collect();

        return view('client.dashboard', compact('widgets', 'recentOrders'));
    }
}
