<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CashTransferRequest;
use App\Models\CashBankTransaction;
use App\Models\CashTransfer;
use App\Services\CashBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CashBankController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService) {}

    public function index(): View
    {
        $cashBalance = $this->cashBankService->getCashBalance();
        $bankBalance = $this->cashBankService->getBankBalance();
        $recentTransactions = $this->cashBankService->getTransactionHistory()->take(30);

        return view('admin.cash-bank.index', compact('cashBalance', 'bankBalance', 'recentTransactions'));
    }

    public function transactions(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->transactionsData($request);
        }

        return view('admin.cash-bank.transactions');
    }

    /**
     * Server-side DataTables feed for the transaction ledger.
     */
    protected function transactionsData(Request $request): JsonResponse
    {
        // Every criterion is applied in SQL here — the type and reference filters
        // used to run over an already-loaded collection, which fell apart as the
        // ledger grew.
        $query = CashBankTransaction::query()->with('creator');

        if ($method = $request->get('method')) {
            $query->where('method', $method);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('transaction_date', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('transaction_date', '<=', $toDate);
        }

        if ($type = $request->get('type')) {
            $query->where('transaction_type', $type);
        }

        if ($referenceType = $request->get('reference_type')) {
            $query->where('reference_type', 'like', "%{$referenceType}%");
        }

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (filled($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('description', 'like', "%{$search}%")
                            ->orWhere('reference_type', 'like', "%{$search}%")
                            ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                    });
                }
            }, true)
            ->addColumn('dated_on', fn (CashBankTransaction $tx) => $tx->transaction_date?->format('Y-m-d'))
            ->addColumn('type_badge', fn (CashBankTransaction $tx) => view('admin.cash-bank.partials.type-cell', ['tx' => $tx])->render())
            ->editColumn('method', fn (CashBankTransaction $tx) => ucfirst(str_replace('_', ' ', $tx->method)))
            ->addColumn('reference', fn (CashBankTransaction $tx) => e(class_basename($tx->reference_type).' #'.$tx->reference_id))
            ->addColumn('created_by_name', fn (CashBankTransaction $tx) => e($tx->creator?->name ?? '—'))
            ->orderColumn('dated_on', 'transaction_date $1')
            ->orderColumn('type_badge', 'transaction_type $1')
            ->orderColumn('reference', 'reference_type $1')
            ->orderColumn('created_by_name', 'created_by $1')
            ->rawColumns(['type_badge'])
            ->toJson();
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
