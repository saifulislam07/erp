<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpensesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Services\CashBankService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService)
    {
    }

    public function index(Request $request): View
    {
        $query = Expense::with('expenseHead');

        if ($headId = $request->get('expense_head_id')) {
            $query->where('expense_head_id', $headId);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        if ($method = $request->get('method')) {
            $query->where('payment_method', $method);
        }

        $expenses = $query->latest()->get();
        $expenseHeads = ExpenseHead::orderBy('name')->get();

        return view('admin.expenses.index', compact('expenses', 'expenseHeads'));
    }

    public function create(): View
    {
        $expenseHeads = ExpenseHead::orderBy('name')->get();

        return view('admin.expenses.create', compact('expenseHeads'));
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $receiptPath = $request->hasFile('receipt_file')
            ? $request->file('receipt_file')->store('expense-receipts', 'public')
            : null;

        $expense = Expense::create([
            'expense_head_id' => $request->expense_head_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'payment_method' => $request->payment_method,
            'description' => $request->description,
            'receipt_file' => $receiptPath,
            'created_by' => $request->user()->id,
        ]);

        $this->cashBankService->debit(
            amount: (float) $expense->amount,
            method: $expense->payment_method,
            referenceType: Expense::class,
            referenceId: $expense->id,
            description: "Expense {$expense->expense_id}",
            userId: $request->user()->id,
        );

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense): View
    {
        $expenseHeads = ExpenseHead::orderBy('name')->get();

        return view('admin.expenses.edit', compact('expense', 'expenseHeads'));
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->cashBankService->credit(
            amount: (float) $expense->amount,
            method: $expense->payment_method,
            referenceType: Expense::class,
            referenceId: $expense->id,
            description: "Reversal of expense {$expense->expense_id}",
            userId: $request->user()->id,
        );

        if ($request->hasFile('receipt_file')) {
            if ($expense->receipt_file) {
                Storage::disk('public')->delete($expense->receipt_file);
            }

            $expense->receipt_file = $request->file('receipt_file')->store('expense-receipts', 'public');
        }

        $expense->update([
            'expense_head_id' => $request->expense_head_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'payment_method' => $request->payment_method,
            'description' => $request->description,
            'receipt_file' => $expense->receipt_file,
        ]);

        $this->cashBankService->debit(
            amount: (float) $expense->amount,
            method: $expense->payment_method,
            referenceType: Expense::class,
            referenceId: $expense->id,
            description: "Updated expense {$expense->expense_id}",
            userId: $request->user()->id,
        );

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $this->cashBankService->credit(
            amount: (float) $expense->amount,
            method: $expense->payment_method,
            referenceType: Expense::class,
            referenceId: $expense->id,
            description: "Reversal of deleted expense {$expense->expense_id}",
            userId: $request->user()->id,
        );

        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted successfully.');
    }

    public function report(Request $request): View
    {
        $expenses = $this->filteredReportQuery($request)->get();
        $expenseHeads = ExpenseHead::orderBy('name')->get();

        $byHead = $expenses->groupBy(fn (Expense $expense) => $expense->expenseHead->name)
            ->map(fn ($group) => $group->sum('amount'));

        return view('admin.expenses.reports.index', [
            'expenses' => $expenses,
            'expenseHeads' => $expenseHeads,
            'byHead' => $byHead,
            'grandTotal' => $expenses->sum('amount'),
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);
    }

    public function reportExcel(Request $request)
    {
        $expenses = $this->filteredReportQuery($request)->get();

        return Excel::download(new ExpensesExport($expenses), 'expenses-report.xlsx');
    }

    public function reportPdf(Request $request)
    {
        $expenses = $this->filteredReportQuery($request)->get();

        $byHead = $expenses->groupBy(fn (Expense $expense) => $expense->expenseHead->name)
            ->map(fn ($group) => $group->sum('amount'));

        $pdf = Pdf::loadView('admin.expenses.reports.pdf', [
            'expenses' => $expenses,
            'byHead' => $byHead,
            'grandTotal' => $expenses->sum('amount'),
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);

        return $pdf->download('expenses-report.pdf');
    }

    private function filteredReportQuery(Request $request)
    {
        $query = Expense::with('expenseHead');

        if ($request->from_date) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->expense_head_id) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        return $query->latest();
    }
}
