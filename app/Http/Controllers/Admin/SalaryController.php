<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalaryRequest;
use App\Models\Salary;
use App\Models\User;
use App\Services\CashBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SalaryController extends Controller
{
    public function __construct(private readonly CashBankService $cashBankService) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData();
        }

        return view('admin.salaries.index');
    }

    /**
     * Server-side DataTables feed for the salary listing.
     */
    protected function indexData(): JsonResponse
    {
        $query = Salary::query()->with('user')->select('salaries.*');

        return DataTables::eloquent($query)
            ->addColumn('employee_name', fn (Salary $salary) => e($salary->user?->name ?? '—'))
            ->editColumn('payment_method', fn (Salary $salary) => ucfirst($salary->payment_method))
            ->addColumn('paid_on', fn (Salary $salary) => $salary->paid_at?->format('Y-m-d') ?? '-')
            ->addColumn('actions', fn (Salary $salary) => view('admin.salaries.partials.actions', compact('salary'))->render())
            ->filterColumn('employee_name', fn ($query, $keyword) => $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$keyword}%")))
            ->orderColumn('employee_name', 'user_id $1')
            ->orderColumn('paid_on', 'paid_at $1')
            ->rawColumns(['actions'])
            ->toJson();
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
