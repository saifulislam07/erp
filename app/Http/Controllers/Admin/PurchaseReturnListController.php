<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * All purchase returns across every purchase.
 *
 * PurchaseReturnController handles returns in the context of one purchase;
 * this is the module-level list the Purchases menu links to.
 */
class PurchaseReturnListController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.purchases.returns.all');
    }

    /**
     * Server-side DataTables feed for the purchase return listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        // The filter bar is the only search on this screen (DataTables' own box is
        // switched off), so every criterion is applied up front — that way the
        // header total below is summed over exactly the rows being listed.
        $query = PurchaseReturn::query()
            ->with(['purchase.supplier', 'items.product', 'creator'])
            ->when($request->get('q'), fn ($q, $search) => $q->where(function ($inner) use ($search) {
                $inner->where('purchase_returns.return_id', 'like', "%{$search}%")
                    ->orWhereHas('purchase', fn ($p) => $p->where('purchase_id', 'like', "%{$search}%"))
                    ->orWhereHas('purchase.supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            }))
            ->when($request->get('from_date'), fn ($q, $date) => $q->whereDate('return_date', '>=', $date))
            ->when($request->get('to_date'), fn ($q, $date) => $q->whereDate('return_date', '<=', $date))
            ->select('purchase_returns.*');

        return DataTables::eloquent($query)
            ->addColumn('returned_on', fn (PurchaseReturn $return) => $return->return_date?->format('d M Y'))
            ->addColumn('purchase_link', fn (PurchaseReturn $return) => view('admin.purchases.returns.partials.purchase-cell', compact('return'))->render())
            ->addColumn('supplier_name', fn (PurchaseReturn $return) => e($return->purchase?->supplier?->name ?? '—'))
            ->addColumn('items_summary', fn (PurchaseReturn $return) => view('admin.purchases.returns.partials.items-cell', compact('return'))->render())
            ->editColumn('reason', fn (PurchaseReturn $return) => e(Str::limit($return->reason, 40)))
            ->editColumn('total_amount', fn (PurchaseReturn $return) => money($return->total_amount))
            ->addColumn('actions', fn (PurchaseReturn $return) => view('admin.purchases.returns.partials.actions', compact('return'))->render())
            ->orderColumn('returned_on', 'return_date $1')
            ->orderColumn('purchase_link', 'purchase_id $1')
            // The header shows what was returned across the whole filtered set,
            // not just the page on screen, so it is summed server-side.
            ->with(['returned_value' => money((float) $query->clone()->sum('total_amount'))])
            ->rawColumns(['purchase_link', 'items_summary', 'actions'])
            ->toJson();
    }
}
