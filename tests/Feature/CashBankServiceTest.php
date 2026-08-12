<?php

namespace Tests\Feature;

use App\Models\CashBankTransaction;
use App\Models\CashTransfer;
use App\Models\OpeningBalance;
use App\Models\User;
use App\Services\CashBankService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the cash and bank ledger: how a balance is derived from the opening
 * figure plus every credit and debit, what a transfer between the two actually
 * writes, and that the running balance stamped on each row matches.
 *
 * Every expense, salary, sale and payment lands here, so a wrong sign or a
 * missed payment method quietly misstates the money on hand.
 */
class CashBankServiceTest extends TestCase
{
    use RefreshDatabase;

    private CashBankService $cashBank;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashBank = app(CashBankService::class);
        $this->user = User::factory()->create();
    }

    public function test_balances_start_at_zero(): void
    {
        $this->assertSame(0.0, $this->cashBank->getCashBalance());
        $this->assertSame(0.0, $this->cashBank->getBankBalance());
    }

    public function test_credits_add_and_debits_subtract(): void
    {
        $this->credit(1000, 'cash');
        $this->debit(250, 'cash');

        $this->assertSame(750.0, $this->cashBank->getCashBalance());
        $this->assertSame(0.0, $this->cashBank->getBankBalance(), 'cash movement must not touch the bank');
    }

    /**
     * `mobile_banking` is a third payment method but not a third bucket — it
     * settles into the bank balance alongside `bank`.
     */
    public function test_mobile_banking_counts_towards_the_bank_balance(): void
    {
        $this->credit(500, 'bank');
        $this->credit(300, 'mobile_banking');
        $this->debit(100, 'mobile_banking');

        $this->assertSame(700.0, $this->cashBank->getBankBalance());
        $this->assertSame(0.0, $this->cashBank->getCashBalance());
    }

    public function test_opening_balances_are_included(): void
    {
        $this->openingBalance('cash', 5000);
        $this->openingBalance('bank', 20000);

        $this->debit(1000, 'cash');
        $this->credit(2500, 'bank');

        $this->assertSame(4000.0, $this->cashBank->getCashBalance());
        $this->assertSame(22500.0, $this->cashBank->getBankBalance());
    }

    /**
     * The balance columns are a snapshot taken as the row is written; the
     * cash/bank history screen prints them rather than recomputing.
     */
    public function test_each_row_records_the_balance_after_it(): void
    {
        $this->openingBalance('cash', 1000);

        $first = $this->credit(500, 'cash');
        $second = $this->debit(200, 'cash');
        $third = $this->credit(700, 'bank');

        $this->assertSame(1500.0, (float) $first->balance_cash_after);
        $this->assertSame(0.0, (float) $first->balance_bank_after);

        $this->assertSame(1300.0, (float) $second->balance_cash_after);

        $this->assertSame(1300.0, (float) $third->balance_cash_after, 'a bank row still carries the cash balance');
        $this->assertSame(700.0, (float) $third->balance_bank_after);
    }

    public function test_a_transfer_moves_money_between_the_two_buckets(): void
    {
        $this->openingBalance('cash', 10000);

        $transfer = $this->cashBank->transfer('cash', 'bank', 4000, 'Deposit at branch', $this->user->id);

        $this->assertInstanceOf(CashTransfer::class, $transfer);
        $this->assertSame(6000.0, $this->cashBank->getCashBalance());
        $this->assertSame(4000.0, $this->cashBank->getBankBalance());
    }

    public function test_a_transfer_writes_a_matched_debit_and_credit(): void
    {
        $transfer = $this->cashBank->transfer('bank', 'cash', 1500, null, $this->user->id);

        $this->assertSame(2, CashBankTransaction::count());

        $debit = CashBankTransaction::where('transaction_type', 'debit')->sole();
        $credit = CashBankTransaction::where('transaction_type', 'credit')->sole();

        $this->assertSame('bank', $debit->method);
        $this->assertSame('cash', $credit->method);
        $this->assertSame(1500.0, (float) $debit->amount);
        $this->assertSame(1500.0, (float) $credit->amount);

        // Both legs point back at the transfer, so the history screen can group them.
        foreach ([$debit, $credit] as $leg) {
            $this->assertSame(CashTransfer::class, $leg->reference_type);
            $this->assertSame($transfer->id, $leg->reference_id);
        }
    }

    public function test_a_transfer_leaves_the_total_unchanged(): void
    {
        $this->openingBalance('cash', 3000);
        $this->openingBalance('bank', 7000);

        $this->cashBank->transfer('bank', 'cash', 2500, null, $this->user->id);

        $this->assertSame(
            10000.0,
            $this->cashBank->getCashBalance() + $this->cashBank->getBankBalance(),
        );
    }

    public function test_history_can_be_filtered_by_method(): void
    {
        $this->credit(100, 'cash');
        $this->credit(200, 'bank');
        $this->credit(300, 'mobile_banking');

        $this->assertCount(3, $this->cashBank->getTransactionHistory());
        $this->assertCount(1, $this->cashBank->getTransactionHistory('cash'));
        $this->assertCount(1, $this->cashBank->getTransactionHistory('mobile_banking'));
    }

    public function test_history_can_be_filtered_by_date_range(): void
    {
        $this->travelTo(now()->subDays(10));
        $this->credit(100, 'cash');

        $this->travelTo(now()->addDays(9));
        $recent = $this->credit(200, 'cash');

        $this->travelBack();

        $window = $this->cashBank->getTransactionHistory(null, now()->subDays(3)->toDateString());

        $this->assertCount(1, $window);
        $this->assertSame($recent->id, $window->first()->id);

        $this->assertCount(
            0,
            $this->cashBank->getTransactionHistory(null, null, now()->subDays(20)->toDateString()),
        );
    }

    public function test_history_is_newest_first(): void
    {
        $older = $this->credit(100, 'cash');
        $newer = $this->credit(200, 'cash');

        $this->assertSame($newer->id, $this->cashBank->getTransactionHistory()->first()->id);
        $this->assertSame($older->id, $this->cashBank->getTransactionHistory()->last()->id);
    }

    private function credit(float $amount, string $method): CashBankTransaction
    {
        return $this->cashBank->credit($amount, $method, null, null, null, $this->user->id);
    }

    private function debit(float $amount, string $method): CashBankTransaction
    {
        return $this->cashBank->debit($amount, $method, null, null, null, $this->user->id);
    }

    private function openingBalance(string $method, float $amount): OpeningBalance
    {
        return OpeningBalance::create([
            'method' => $method,
            'amount' => $amount,
            'date' => now()->toDateString(),
            'set_by' => $this->user->id,
        ]);
    }
}
