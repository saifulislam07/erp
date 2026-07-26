<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $expenses)
    {
    }

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return ['Expense ID', 'Head', 'Amount', 'Date', 'Method', 'Description'];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_id,
            $expense->expenseHead?->name,
            $expense->amount,
            $expense->expense_date->format('Y-m-d'),
            ucfirst($expense->payment_method),
            $expense->description,
        ];
    }
}
