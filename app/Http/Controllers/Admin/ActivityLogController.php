<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    private const array LOGGED_MODELS = [
        'Product' => Product::class,
        'Purchase' => Purchase::class,
        'Sale' => Sale::class,
        'Order' => Order::class,
        'Client' => Client::class,
        'Expense' => Expense::class,
        'Asset' => Asset::class,
    ];

    public function index(Request $request): View
    {
        $query = Activity::with('causer')->latest();

        if ($subjectType = $request->get('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        if ($causerId = $request->get('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $activities = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.activity-log.index', [
            'activities' => $activities,
            'users' => $users,
            'models' => self::LOGGED_MODELS,
        ]);
    }
}
