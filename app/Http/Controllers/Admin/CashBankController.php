<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CashTransferRequest;
use App\Models\CashTransfer;
use App\Services\CashBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashBankController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService)
    {
    }

    public function index(): View
    {
        $cashBalance = $this->cashBankService->getCashBalance();
        $bankBalance = $this->cashBankService->getBankBalance();
        $recentTransactions = $this->cashBankService->getTransactionHistory()->take(30);

        return view('admin.cash-bank.index', compact('cashBalance', 'bankBalance', 'recentTransactions'));
    }

    public function transactions(Request $request): View
    {
        $transactions = $this->cashBankService->getTransactionHistory(
            $request->get('method'),
            $request->get('from_date'),
            $request->get('to_date'),
        );

        if ($type = $request->get('type')) {
            $transactions = $transactions->where('transaction_type', $type);
        }

        if ($referenceType = $request->get('reference_type')) {
            $transactions = $transactions->filter(fn ($t) => str_contains((string) $t->reference_type, $referenceType));
        }

        return view('admin.cash-bank.transactions', compact('transactions'));
    }

    public function transferForm(): View
    {
        $cashBalance = $this->cashBankService->getCashBalance();
        $bankBalance = $this->cashBankService->getBankBalance();

        return view('admin.cash-bank.transfer', compact('cashBalance', 'bankBalance'));
    }

    public function transfer(CashTransferRequest $request): RedirectResponse
    {
        $this->cashBankService->transfer(
            $request->from_method,
            $request->to_method,
            $request->amount,
            $request->note,
            $request->user()->id,
        );

        return redirect()->route('admin.cash-bank.index')->with('success', 'Transfer completed successfully.');
    }

    public function transferHistory(): View
    {
        $transfers = CashTransfer::with('creator')->latest()->get();

        return view('admin.cash-bank.transfer-history', compact('transfers'));
    }
}
