<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountRequest;
use App\Models\Account;
use App\Models\Client;
use App\Models\Supplier;
use App\Services\CashBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService)
    {
    }

    public function payable(Request $request): View
    {
        abort_unless($request->user()->is_admin || $request->user()->hasRole('Accountant'), 403);

        $query = Account::where('type', 'payable')->where('party_type', 'supplier');

        $this->applyCommonFilters($query, $request);

        $accounts = $query->latest()->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.accounts.payable', compact('accounts', 'suppliers'));
    }

    public function receivable(Request $request): View
    {
        abort_unless($request->user()->is_admin || $request->user()->hasRole('Accountant'), 403);

        $query = Account::where('type', 'receivable')->where('party_type', 'client');

        $this->applyCommonFilters($query, $request);

        $accounts = $query->latest()->get();
        $clients = Client::orderBy('name')->get();

        return view('admin.accounts.receivable', compact('accounts', 'clients'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->is_admin, 403);

        $suppliers = Supplier::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        $type = $request->get('type', 'payable');

        return view('admin.accounts.create', compact('suppliers', 'clients', 'type'));
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        Account::create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()->route('admin.accounts.'.$request->type)->with('success', 'Account entry created successfully.');
    }

    public function edit(Request $request, Account $account): View
    {
        abort_unless($request->user()->is_admin, 403);

        $suppliers = Supplier::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        return view('admin.accounts.edit', compact('account', 'suppliers', 'clients'));
    }

    public function update(AccountRequest $request, Account $account): RedirectResponse
    {
        $account->update($request->validated() + ['updated_by' => $request->user()->id]);

        return redirect()->route('admin.accounts.'.$account->type)->with('success', 'Account entry updated successfully.');
    }

    public function destroy(Request $request, Account $account): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $type = $account->type;
        $account->delete();

        return redirect()->route('admin.accounts.'.$type)->with('success', 'Account entry deleted successfully.');
    }

    public function settle(Request $request, Account $account): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        if ($account->is_settled) {
            return back()->with('error', 'This account entry is already settled.');
        }

        $request->validate([
            'method' => ['required', 'in:cash,bank,mobile_banking'],
        ]);

        if ($account->type === 'payable') {
            $this->cashBankService->debit(
                amount: (float) $account->amount,
                method: $request->method,
                referenceType: Account::class,
                referenceId: $account->id,
                description: "Settled payable #{$account->id}",
                userId: $request->user()->id,
            );
        } else {
            $this->cashBankService->credit(
                amount: (float) $account->amount,
                method: $request->method,
                referenceType: Account::class,
                referenceId: $account->id,
                description: "Settled receivable #{$account->id}",
                userId: $request->user()->id,
            );
        }

        $account->update([
            'is_settled' => true,
            'settled_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Account settled successfully.');
    }

    private function applyCommonFilters($query, Request $request): void
    {
        if ($request->filled('settled')) {
            $query->where('is_settled', $request->get('settled') === '1');
        }

        if ($partyId = $request->get('party_id')) {
            $query->where('party_id', $partyId);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }
    }
}
