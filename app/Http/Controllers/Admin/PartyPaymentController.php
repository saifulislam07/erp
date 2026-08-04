<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartyPaymentRequest;
use App\Models\Client;
use App\Models\PartyPayment;
use App\Models\Supplier;
use App\Services\PartyLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use RuntimeException;

/**
 * Shared behaviour for the supplier-payable and customer-receivable screens.
 *
 * The two sides are the same workflow with the sign flipped, so the subclasses
 * only declare which party they operate on and how it should be worded.
 */
abstract class PartyPaymentController extends Controller
{
    public function __construct(protected readonly PartyLedgerService $ledger) {}

    /**
     * PartyPayment::TYPE_SUPPLIER or PartyPayment::TYPE_CLIENT.
     */
    abstract protected function partyType(): string;

    /**
     * Route-name prefix, e.g. 'admin.supplier-payments'.
     */
    abstract protected function routePrefix(): string;

    /**
     * Wording for the views.
     *
     * @return array<string, string>
     */
    abstract protected function labels(): array;

    /**
     * Balance sheet: who owes what.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeArea();

        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.party-payments.index', [
            'labels' => $this->labels(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    /**
     * Server-side DataTables feed for the balances screen.
     *
     * Each row's balance is derived across purchases/returns/payments rather than
     * stored, so it comes from the ledger service exactly as before and is paged
     * as a collection — the browser still only receives one page at a time, and
     * the money maths stays in the one place that is tested.
     */
    protected function indexData(Request $request): JsonResponse
    {
        $rows = $this->balances($request);
        $labels = $this->labels();
        $routePrefix = $this->routePrefix();

        return DataTables::collection($rows)
            ->addColumn('party_name', fn ($row) => view('admin.party-payments.partials.balance-party-cell', compact('row', 'routePrefix'))->render())
            ->addColumn('billed_value', fn ($row) => money($row->billed))
            ->addColumn('returned_value', fn ($row) => $row->returned > 0 ? money($row->returned) : '—')
            ->addColumn('paid_value', fn ($row) => view('admin.party-payments.partials.balance-paid-cell', compact('row'))->render())
            ->addColumn('balance_value', fn ($row) => view('admin.party-payments.partials.balance-cell', compact('row', 'labels'))->render())
            ->addColumn('actions', fn ($row) => view('admin.party-payments.partials.balance-actions', compact('row', 'routePrefix', 'labels'))->render())
            ->with([
                'total_due' => money((float) $rows->sum('balance')),
                'total_advance' => money((float) $rows->sum('advance')),
                'with_balance' => $rows->filter(fn ($row) => $row->balance > 0.009)->count(),
                'total_parties' => $rows->count(),
            ])
            ->rawColumns(['party_name', 'paid_value', 'balance_value', 'actions'])
            ->toJson();
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function balances(Request $request): \Illuminate\Support\Collection
    {
        $rows = $this->partyType() === PartyPayment::TYPE_SUPPLIER
            ? $this->ledger->supplierBalances($request->get('q'))
            : $this->ledger->customerBalances($request->get('q'));

        if ($request->boolean('outstanding')) {
            $rows = $rows->filter(fn ($row) => $row->balance > 0.009)->values();
        }

        return $rows;
    }

    /**
     * One party's statement: invoices, payments and the running balance.
     */
    public function ledger(int $party): View
    {
        $this->authorizeArea();

        $model = $this->findParty($party);

        return view('admin.party-payments.ledger', [
            'party' => $model,
            'invoices' => $this->ledger->invoices($this->partyType(), $model->id),
            'payments' => $this->ledger->payments($this->partyType(), $model->id),
            'balance' => $this->ledger->balanceFor($this->partyType(), $model->id),
            'labels' => $this->labels(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    /**
     * Form for taking or making a payment.
     */
    public function create(int $party): View
    {
        $this->authorizeArea();

        $model = $this->findParty($party);

        return view('admin.party-payments.create', [
            'party' => $model,
            'openInvoices' => $this->ledger->openInvoices($this->partyType(), $model->id),
            'balance' => $this->ledger->balanceFor($this->partyType(), $model->id),
            'labels' => $this->labels(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(PartyPaymentRequest $request, int $party): RedirectResponse
    {
        $this->authorizeArea();

        $model = $this->findParty($party);

        try {
            $payment = $this->ledger->recordPayment(
                $request->validated() + [
                    'party_type' => $this->partyType(),
                    'party_id' => $model->id,
                ],
                $request->user()->id,
            );
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route($this->routePrefix().'.ledger', $model->id)
            ->with('success', $this->labels()['recorded'].' '.$payment->payment_id.'.');
    }

    /**
     * Every payment on this side, newest first.
     */
    public function history(Request $request): View|JsonResponse
    {
        $this->authorizeArea();

        if ($request->ajax()) {
            return $this->historyData($request);
        }

        return view('admin.party-payments.history', [
            'labels' => $this->labels(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    /**
     * Server-side DataTables feed for the payment history.
     */
    protected function historyData(Request $request): JsonResponse
    {
        $query = PartyPayment::query()
            ->with(['allocations', 'creator'])
            ->where('party_type', $this->partyType())
            ->when($request->get('from_date'), fn ($q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when($request->get('to_date'), fn ($q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->when($request->get('method'), fn ($q, $method) => $q->where('method', $method));

        $labels = $this->labels();
        $routePrefix = $this->routePrefix();
        $partyModel = $this->partyType() === PartyPayment::TYPE_SUPPLIER ? Supplier::class : Client::class;

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (filled($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('payment_id', 'like', "%{$search}%")
                            ->orWhere('reference', 'like', "%{$search}%");
                    });
                }
            }, true)
            ->addColumn('paid_on', fn (PartyPayment $payment) => $payment->payment_date?->format('d M Y'))
            ->addColumn('reference_cell', fn (PartyPayment $payment) => view('admin.party-payments.partials.reference-cell', compact('payment'))->render())
            // Resolved per row rather than in one pass, but only the current page
            // is ever rendered so this is a handful of lookups, not a full table.
            ->addColumn('party_name', fn (PartyPayment $payment) => view('admin.party-payments.partials.party-cell', [
                'payment' => $payment,
                'routePrefix' => $routePrefix,
                'labels' => $labels,
                'name' => $partyModel::whereKey($payment->party_id)->value('name'),
            ])->render())
            ->editColumn('method', fn (PartyPayment $payment) => ucwords(str_replace('_', ' ', $payment->method)))
            ->addColumn('applied_to', fn (PartyPayment $payment) => view('admin.party-payments.partials.applied-cell', compact('payment'))->render())
            ->editColumn('amount', fn (PartyPayment $payment) => money($payment->amount))
            ->addColumn('actions', fn (PartyPayment $payment) => view('admin.party-payments.partials.actions', compact('payment', 'routePrefix'))->render())
            ->orderColumn('paid_on', 'payment_date $1')
            ->orderColumn('reference_cell', 'payment_id $1')
            ->orderColumn('party_name', 'party_id $1')
            ->with(['paid_value' => money((float) $query->clone()->sum('amount'))])
            ->rawColumns(['reference_cell', 'party_name', 'applied_to', 'actions'])
            ->toJson();
    }

    /**
     * Reverse a payment. Admin-only: it moves money in the cash ledger.
     */
    public function destroy(Request $request, PartyPayment $payment): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403, 'Only an administrator can reverse a payment.');
        abort_unless($payment->party_type === $this->partyType(), 404);

        $partyId = $payment->party_id;

        $this->ledger->deletePayment($payment, $request->user()->id);

        return redirect()
            ->route($this->routePrefix().'.ledger', $partyId)
            ->with('success', 'Payment '.$payment->payment_id.' was reversed.');
    }

    protected function findParty(int $id): Supplier|Client
    {
        return $this->partyType() === PartyPayment::TYPE_SUPPLIER
            ? Supplier::findOrFail($id)
            : Client::findOrFail($id);
    }

    /**
     * Both screens sit behind the module permission of the side they report on.
     */
    protected function authorizeArea(): void
    {
        $permission = $this->partyType() === PartyPayment::TYPE_SUPPLIER ? 'purchase.view' : 'sale.view';

        abort_unless(
            auth()->user()->is_admin || auth()->user()->can($permission),
            403,
        );
    }
}
