<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    private const array LOGGED_MODELS = [
        'Product' => \App\Models\Product::class,
        'Purchase' => \App\Models\Purchase::class,
        'Sale' => \App\Models\Sale::class,
        'Order' => \App\Models\Order::class,
        'Client' => \App\Models\Client::class,
        'Expense' => \App\Models\Expense::class,
        'Asset' => \App\Models\Asset::class,
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
