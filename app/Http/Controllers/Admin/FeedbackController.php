<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class FeedbackController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.feedbacks.index');
    }

    /**
     * Server-side DataTables feed for the feedback listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        $query = Feedback::query()->with(['order', 'client'])->select('feedbacks.*');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($rating = $request->get('rating')) {
            $query->where('rating', $rating);
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
                        $q->where('feedbacks.comment', 'like', "%{$search}%")
                            ->orWhereHas('order', fn ($o) => $o->where('order_id', 'like', "%{$search}%"))
                            ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                    });
                }
            }, true)
            ->addColumn('order_label', fn (Feedback $feedback) => e($feedback->order?->order_id ?? '—'))
            ->addColumn('client_name', fn (Feedback $feedback) => e($feedback->client?->name ?? '—'))
            ->editColumn('type', fn (Feedback $feedback) => ucfirst($feedback->type))
            ->editColumn('rating', fn (Feedback $feedback) => $feedback->rating.' / 5')
            ->addColumn('left_on', fn (Feedback $feedback) => $feedback->created_at?->format('Y-m-d'))
            ->orderColumn('order_label', 'order_id $1')
            ->orderColumn('client_name', 'client_id $1')
            ->orderColumn('left_on', 'created_at $1')
            ->toJson();
    }
}
