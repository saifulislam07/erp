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
 * The single ledger every other module posts to. Two details here are easy to
 * get wrong and expensive when they are: mobile banking counts towards the
 * bank balance rather than a third bucket of its own, and the running balance
 * stamped on each row is the balance *after* that row.
 */
class CashBankServiceTest extends TestCase
{
    use RefreshDatabase;

    private CashBankService $ledger;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledger = app(CashBankService::class);
        $this->user = User::factory()->create();
    }

    public function test_both_balances_start_empty(): void
    {
        $this->assertSame(0.0, $this->ledger->getCashBalance());
        $this->assertSame(0.0, $this->ledger->getBankBalance());
    }

    public function test_an_opening_balance_is_the_starting_point(): void
    {
        $this->openingBalance('cash', 5000);
        $this->openingBalance('bank', 20000);

        $this->assertSame(5000.0, $this->ledger->getCashBalance());
        $this->assertSame(20000.0, $this->ledger->getBankBalance());
    }

    public function test_credits_add_and_debits_subtract(): void
    {
        $this->openingBalance('cash', 1000);

        $this->credit(500, 'cash');
        $this->debit(200, 'cash');

        $this->assertSame(1300.0, $this->ledger->getCashBalance());
    }

    public function test_the_two_buckets_do_not_leak_into_each_other(): void
    {
        $this->credit(700, 'cash');
        $this->credit(400, 'bank');

        $this->assertSame(700.0, $this->ledger->getCashBalance());
        $this->assertSame(400.0, $this->ledger->getBankBalance());
    }

    public function test_mobile_banking_lands_in_the_bank_balance(): void
    {
        // There is no separate mobile-banking bucket; it settles into the bank.
        $this->credit(900, 'mobile_banking');

        $this->assertSame(900.0, $this->ledger->getBankBalance());
        $this->assertSame(0.0, $this->ledger->getCashBalance());
    }

    public function test_each_row_records_the_balance_after_it(): void
    {
        $this->credit(1000, 'cash');
        $this->debit(250, 'cash');

        $latest = CashBankTransaction::latest('id')->first();

        $this->assertSame('750.00', $latest->balance_cash_after);
        $this->assertSame('0.00', $latest->balance_bank_after);
    }

    public function test_a_transfer_moves_money_without_changing_the_total(): void
    {
        $this->openingBalance('cash', 3000);
        $totalBefore = $this->ledger->getCashBalance() + $this->ledger->getBankBalance();

        $this->ledger->transfer('cash', 'bank', 1200, 'Deposited the day\'s takings', $this->user->id);

        $this->assertSame(1800.0, $this->ledger->getCashBalance());
        $this->assertSame(1200.0, $this->ledger->getBankBalance());
        $this->assertSame($totalBefore, $this->ledger->getCashBalance() + $this->ledger->getBankBalance());
    }

    public function test_a_transfer_leaves_a_matched_pair_of_entries(): void
    {
        $transfer = $this->ledger->transfer('bank', 'cash', 500, null, $this->user->id);

        $rows = CashBankTransaction::where('reference_type', CashTransfer::class)
            ->where('reference_id', $transfer->id)
            ->get();

        $this->assertCount(2, $rows);
        $this->assertSame(500.0, (float) $rows->firstWhere('transaction_type', 'debit')->amount);
        $this->assertSame(500.0, (float) $rows->firstWhere('transaction_type', 'credit')->amount);
    }

    public function test_history_can_be_narrowed_to_one_method(): void
    {
        $this->credit(100, 'cash');
        $this->credit(200, 'bank');
        $this->credit(300, 'cash');

        $this->assertCount(2, $this->ledger->getTransactionHistory('cash'));
        $this->assertCount(3, $this->ledger->getTransactionHistory());
    }

    public function test_history_can_be_narrowed_to_a_date_range(): void
    {
        $this->credit(100, 'cash');

        CashBankTransaction::query()->update(['transaction_date' => now()->subMonth()->toDateString()]);

        $this->credit(200, 'cash');

        $this->assertCount(1, $this->ledger->getTransactionHistory(null, now()->subWeek()->toDateString()));
    }

    private function openingBalance(string $method, float $amount): void
    {
        OpeningBalance::create([
            'method' => $method,
            'amount' => $amount,
            'date' => now()->toDateString(),
            'set_by' => $this->user->id,
        ]);
    }

    private function credit(float $amount, string $method): void
    {
        $this->ledger->credit($amount, $method, null, null, 'Test credit', $this->user->id);
    }

    private function debit(float $amount, string $method): void
    {
        $this->ledger->debit($amount, $method, null, null, 'Test debit', $this->user->id);
    }
}
