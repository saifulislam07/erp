<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalaryRequest;
use App\Models\Salary;
use App\Models\User;
use App\Services\CashBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SalaryController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService)
    {
    }

    public function index(): View
    {
        $salaries = Salary::with('user')->latest()->get();

        return view('admin.salaries.index', compact('salaries'));
    }

    public function create(): View
    {
        $users = User::where('is_admin', false)->where('status', true)->orderBy('name')->get();

        return view('admin.salaries.create', compact('users'));
    }

    public function store(SalaryRequest $request): RedirectResponse
    {
        $netSalary = $request->basic_salary - ($request->deduction ?? 0);

        $salary = Salary::create([
            'user_id' => $request->user_id,
            'month' => $request->month,
            'basic_salary' => $request->basic_salary,
            'deduction' => $request->deduction ?? 0,
            'net_salary' => $netSalary,
            'payment_method' => $request->payment_method,
            'paid_at' => now(),
            'note' => $request->note,
            'created_by' => $request->user()->id,
        ]);

        $this->cashBankService->debit(
            amount: (float) $salary->net_salary,
            method: $salary->payment_method,
            referenceType: Salary::class,
            referenceId: $salary->id,
            description: "Salary payment for {$salary->user->name} ({$salary->month})",
            userId: $request->user()->id,
        );

        return redirect()->route('admin.salaries.index')->with('success', 'Salary paid successfully.');
    }

    public function show(Salary $salary): View
    {
        $salary->load('user', 'creator');

        return view('admin.salaries.show', compact('salary'));
    }
}
