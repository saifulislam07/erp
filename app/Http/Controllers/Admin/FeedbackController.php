<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = Feedback::with(['order', 'client']);

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

        $feedbacks = $query->latest()->get();

        return view('admin.feedbacks.index', compact('feedbacks'));
    }
}
