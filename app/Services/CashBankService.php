<?php

namespace App\Services;

use App\Models\CashBankTransaction;
use App\Models\CashTransfer;
use App\Models\OpeningBalance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CashBankService
{
    private const BANK_METHODS = ['bank', 'mobile_banking'];

    public function debit(
        float $amount,
        string $method,
        ?string $referenceType,
        ?int $referenceId,
        ?string $description,
        int $userId,
    ): CashBankTransaction {
        return $this->recordTransaction('debit', $amount, $method, $referenceType, $referenceId, $description, $userId);
    }

    public function credit(
        float $amount,
        string $method,
        ?string $referenceType,
        ?int $referenceId,
        ?string $description,
        int $userId,
    ): CashBankTransaction {
        return $this->recordTransaction('credit', $amount, $method, $referenceType, $referenceId, $description, $userId);
    }

    public function transfer(string $fromMethod, string $toMethod, float $amount, ?string $note, int $userId): CashTransfer
    {
        return DB::transaction(function () use ($fromMethod, $toMethod, $amount, $note, $userId) {
            $transfer = CashTransfer::create([
                'from_method' => $fromMethod,
                'to_method' => $toMethod,
                'amount' => $amount,
                'transfer_date' => now()->toDateString(),
                'note' => $note,
                'created_by' => $userId,
            ]);

            $this->debit($amount, $fromMethod, CashTransfer::class, $transfer->id, $note ?? "Transfer {$transfer->transfer_id}", $userId);
            $this->credit($amount, $toMethod, CashTransfer::class, $transfer->id, $note ?? "Transfer {$transfer->transfer_id}", $userId);

            return $transfer;
        });
    }

    public function getCashBalance(): float
    {
        return $this->currentBalance('cash');
    }

    public function getBankBalance(): float
    {
        return $this->currentBalance('bank');
    }

    public function getTransactionHistory(?string $method = null, ?string $fromDate = null, ?string $toDate = null): Collection
    {
        $query = CashBankTransaction::with('creator')->latest();

        if ($method) {
            $query->where('method', $method);
        }

        if ($fromDate) {
            $query->whereDate('transaction_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('transaction_date', '<=', $toDate);
        }

        return $query->get();
    }

    private function recordTransaction(
        string $type,
        float $amount,
        string $method,
        ?string $referenceType,
        ?int $referenceId,
        ?string $description,
        int $userId,
    ): CashBankTransaction {
        $cashBalance = $this->getCashBalance();
        $bankBalance = $this->getBankBalance();

        $delta = $type === 'credit' ? $amount : -$amount;

        if ($method === 'cash') {
            $cashBalance += $delta;
        } else {
            $bankBalance += $delta;
        }

        return CashBankTransaction::create([
            'transaction_type' => $type,
            'method' => $method,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'transaction_date' => now()->toDateString(),
            'created_by' => $userId,
            'balance_cash_after' => $cashBalance,
            'balance_bank_after' => $bankBalance,
        ]);
    }

    private function currentBalance(string $bucket): float
    {
        $methods = $bucket === 'cash' ? ['cash'] : self::BANK_METHODS;

        $opening = (float) OpeningBalance::where('method', $bucket)->sum('amount');

        $credits = (float) CashBankTransaction::whereIn('method', $methods)
            ->where('transaction_type', 'credit')
            ->sum('amount');

        $debits = (float) CashBankTransaction::whereIn('method', $methods)
            ->where('transaction_type', 'debit')
            ->sum('amount');

        return $opening + $credits - $debits;
    }
}
