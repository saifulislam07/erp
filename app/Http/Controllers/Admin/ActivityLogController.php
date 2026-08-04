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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

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

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.activity-log.index', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'models' => self::LOGGED_MODELS,
        ]);
    }

    /**
     * Server-side DataTables feed for the activity log.
     */
    protected function indexData(Request $request): JsonResponse
    {
        $query = Activity::query()->with('causer');

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

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (filled($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('description', 'like', "%{$search}%")
                            ->orWhere('event', 'like', "%{$search}%")
                            ->orWhereHas('causer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                    });
                }
            }, true)
            ->addColumn('logged_at', fn (Activity $activity) => $activity->created_at?->format('Y-m-d H:i'))
            ->addColumn('causer_name', fn (Activity $activity) => e($activity->causer?->name ?? 'System'))
            ->addColumn('event_badge', fn (Activity $activity) => '<span class="badge badge-secondary">'.e(ucfirst($activity->event ?? '')).'</span>')
            ->addColumn('module', fn (Activity $activity) => e(class_basename($activity->subject_type).' #'.$activity->subject_id))
            ->orderColumn('logged_at', 'created_at $1')
            ->orderColumn('causer_name', 'causer_id $1')
            ->orderColumn('event_badge', 'event $1')
            ->orderColumn('module', 'subject_type $1')
            ->rawColumns(['event_badge'])
            ->toJson();
    }
}
