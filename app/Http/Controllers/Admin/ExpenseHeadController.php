<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseHeadRequest;
use App\Models\ExpenseHead;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseHeadController extends Controller
{
    public function index(): View
    {
        $expenseHeads = ExpenseHead::withCount('expenses')->orderBy('name')->get();

        return view('admin.expense-heads.index', compact('expenseHeads'));
    }

    public function create(): View
    {
        return view('admin.expense-heads.create');
    }

    public function store(ExpenseHeadRequest $request): RedirectResponse
    {
        ExpenseHead::create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()->route('admin.expense-heads.index')->with('success', 'Expense head created successfully.');
    }

    public function edit(ExpenseHead $expenseHead): View
    {
        return view('admin.expense-heads.edit', compact('expenseHead'));
    }

    public function update(ExpenseHeadRequest $request, ExpenseHead $expenseHead): RedirectResponse
    {
        $expenseHead->update($request->validated());

        return redirect()->route('admin.expense-heads.index')->with('success', 'Expense head updated successfully.');
    }

    public function destroy(ExpenseHead $expenseHead): RedirectResponse
    {
        if ($expenseHead->expenses()->exists()) {
            return redirect()->route('admin.expense-heads.index')->with('error', 'Cannot delete an expense head that has expenses.');
        }

        $expenseHead->delete();

        return redirect()->route('admin.expense-heads.index')->with('success', 'Expense head deleted successfully.');
    }
}
