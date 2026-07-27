<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * All purchase returns across every purchase.
 *
 * PurchaseReturnController handles returns in the context of one purchase;
 * this is the module-level list the Purchases menu links to.
 */
class PurchaseReturnListController extends Controller
{
    public function index(Request $request): View
    {
        $returns = PurchaseReturn::with(['purchase.supplier', 'items.product', 'creator'])
            ->when($request->get('q'), fn ($q, $search) => $q->where(function ($inner) use ($search) {
                $inner->where('return_id', 'like', "%{$search}%")
                    ->orWhereHas('purchase', fn ($p) => $p->where('purchase_id', 'like', "%{$search}%"))
                    ->orWhereHas('purchase.supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            }))
            ->when($request->get('from_date'), fn ($q, $date) => $q->whereDate('return_date', '>=', $date))
            ->when($request->get('to_date'), fn ($q, $date) => $q->whereDate('return_date', '<=', $date))
            ->latest('return_date')
            ->latest('id')
            ->get();

        return view('admin.purchases.returns.all', [
            'returns' => $returns,
            'filters' => $request->only(['q', 'from_date', 'to_date']),
        ]);
    }
}
