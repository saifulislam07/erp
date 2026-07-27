<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartyPaymentRequest;
use App\Models\Client;
use App\Models\PartyPayment;
use App\Models\Supplier;
use App\Services\PartyLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
    public function index(Request $request): View
    {
        $this->authorizeArea();

        $search = $request->get('q');

        $rows = $this->partyType() === PartyPayment::TYPE_SUPPLIER
            ? $this->ledger->supplierBalances($search)
            : $this->ledger->customerBalances($search);

        if ($request->boolean('outstanding')) {
            $rows = $rows->filter(fn ($row) => $row->balance > 0.009)->values();
        }

        return view('admin.party-payments.index', [
            'rows' => $rows,
            'labels' => $this->labels(),
            'routePrefix' => $this->routePrefix(),
            'search' => $search,
        ]);
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
    public function history(Request $request): View
    {
        $this->authorizeArea();

        $payments = PartyPayment::with(['allocations', 'creator'])
            ->where('party_type', $this->partyType())
            ->when($request->get('from_date'), fn ($q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when($request->get('to_date'), fn ($q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->when($request->get('method'), fn ($q, $method) => $q->where('method', $method))
            ->latest('payment_date')
            ->latest('id')
            ->get();

        // Resolve the party names in one pass instead of per row.
        $names = $this->partyType() === PartyPayment::TYPE_SUPPLIER
            ? Supplier::whereIn('id', $payments->pluck('party_id'))->pluck('name', 'id')
            : Client::whereIn('id', $payments->pluck('party_id'))->pluck('name', 'id');

        return view('admin.party-payments.history', [
            'payments' => $payments,
            'names' => $names,
            'labels' => $this->labels(),
            'routePrefix' => $this->routePrefix(),
            'filters' => $request->only(['from_date', 'to_date', 'method']),
        ]);
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
