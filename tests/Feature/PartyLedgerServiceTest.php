<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PartyPayment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CashBankService;
use App\Services\PartyLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the money maths behind the supplier/customer due screens: how a
 * balance is derived, how a payment is spread over open invoices, and that
 * reversing one puts everything back exactly as it was.
 */
class PartyLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    private PartyLedgerService $ledger;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledger = app(PartyLedgerService::class);
        $this->user = User::factory()->create();
    }

    public function test_supplier_balance_is_billed_minus_returned_minus_paid(): void
    {
        $supplier = $this->supplier();

        $purchase = $this->purchase($supplier, total: 10000, paid: 4000);

        PurchaseReturn::create([
            'purchase_id' => $purchase->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged carton',
            'total_amount' => 1500,
            'created_by' => $this->user->id,
        ]);

        // 10000 billed − 1500 returned − 4000 paid
        $this->assertSame(4500.0, $this->ledger->balanceFor(PartyPayment::TYPE_SUPPLIER, $supplier->id));
    }

    public function test_payment_is_applied_to_the_oldest_invoice_first(): void
    {
        $supplier = $this->supplier();

        $older = $this->purchase($supplier, total: 3000, paid: 0, date: now()->subDays(10));
        $newer = $this->purchase($supplier, total: 5000, paid: 0, date: now()->subDays(2));

        $this->ledger->recordPayment([
            'party_type' => PartyPayment::TYPE_SUPPLIER,
            'party_id' => $supplier->id,
            'payment_date' => now()->toDateString(),
            'amount' => 4000,
            'method' => 'cash',
        ], $this->user->id);

        // The older invoice is cleared in full, the remainder lands on the newer one.
        $this->assertSame('3000.00', $older->fresh()->paid_amount);
        $this->assertSame('paid', $older->fresh()->payment_status);
        $this->assertSame('1000.00', $newer->fresh()->paid_amount);
        $this->assertSame('partial', $newer->fresh()->payment_status);
    }

    public function test_payment_beyond_the_outstanding_total_is_kept_as_an_advance(): void
    {
        $supplier = $this->supplier();
        $this->purchase($supplier, total: 1000, paid: 0);

        $payment = $this->ledger->recordPayment([
            'party_type' => PartyPayment::TYPE_SUPPLIER,
            'party_id' => $supplier->id,
            'payment_date' => now()->toDateString(),
            'amount' => 2500,
            'method' => 'bank',
        ], $this->user->id);

        $this->assertSame(1000.0, (float) $payment->allocations->sum('amount'));
        $this->assertSame(1500.0, $payment->fresh()->load('allocations')->unallocated_amount);
    }

    public function test_supplier_payment_takes_money_out_of_cash(): void
    {
        $supplier = $this->supplier();
        $this->purchase($supplier, total: 1000, paid: 0);

        $before = app(CashBankService::class)->getCashBalance();

        $this->ledger->recordPayment([
            'party_type' => PartyPayment::TYPE_SUPPLIER,
            'party_id' => $supplier->id,
            'payment_date' => now()->toDateString(),
            'amount' => 600,
            'method' => 'cash',
        ], $this->user->id);

        $this->assertSame($before - 600, app(CashBankService::class)->getCashBalance());
    }

    public function test_reversing_a_payment_restores_the_invoice_and_the_cash_balance(): void
    {
        $supplier = $this->supplier();
        $purchase = $this->purchase($supplier, total: 4000, paid: 1000);

        $cashBefore = app(CashBankService::class)->getCashBalance();

        $payment = $this->ledger->recordPayment([
            'party_type' => PartyPayment::TYPE_SUPPLIER,
            'party_id' => $supplier->id,
            'payment_date' => now()->toDateString(),
            'amount' => 2000,
            'method' => 'cash',
        ], $this->user->id);

        $this->assertSame('3000.00', $purchase->fresh()->paid_amount);

        $this->ledger->deletePayment($payment, $this->user->id);

        $this->assertSame('1000.00', $purchase->fresh()->paid_amount);
        $this->assertSame('3000.00', $purchase->fresh()->due_amount);
        $this->assertSame($cashBefore, app(CashBankService::class)->getCashBalance());
        $this->assertSame(0, PartyPayment::count());
    }

    public function test_customer_balance_accounts_for_returns_and_refunds(): void
    {
        $client = Client::create([
            'name' => 'Bright Retail',
            'email' => 'bright@example.com',
            'password' => 'secret-password',
            'type' => 'client',
            'status' => true,
        ]);

        $sale = $this->sale($client, total: 1000, paid: 1000);

        // Goods worth 300 come back; 100 of that is handed over the counter.
        SaleReturn::create([
            'sale_id' => $sale->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Wrong size',
            'total_amount' => 300,
            'refund_amount' => 100,
            'refund_method' => 'cash',
            'restock' => true,
            'created_by' => $this->user->id,
        ]);

        // 1000 billed − 300 returned − 1000 received + 100 refunded = −200,
        // i.e. the shop still owes the customer 200.
        $this->assertSame(-200.0, $this->ledger->balanceFor(PartyPayment::TYPE_CLIENT, $client->id));
    }

    public function test_a_zero_payment_is_rejected(): void
    {
        $supplier = $this->supplier();

        $this->expectException(\RuntimeException::class);

        $this->ledger->recordPayment([
            'party_type' => PartyPayment::TYPE_SUPPLIER,
            'party_id' => $supplier->id,
            'payment_date' => now()->toDateString(),
            'amount' => 0,
            'method' => 'cash',
        ], $this->user->id);
    }

    private function supplier(): Supplier
    {
        return Supplier::create([
            'name' => 'Acme Supplies',
            'email' => 'acme@example.com',
            'phone' => '01700000000',
            'address' => '1 Trade Road',
            'company_name' => 'Acme Ltd',
            'status' => true,
        ]);
    }

    private function purchase(Supplier $supplier, float $total, float $paid, ?\DateTimeInterface $date = null): Purchase
    {
        return Purchase::create([
            'supplier_id' => $supplier->id,
            'purchase_date' => ($date ?? now())->format('Y-m-d'),
            'subtotal' => $total,
            'vat_amount' => 0,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
        ]);
    }

    private function sale(Client $client, float $total, float $paid): Sale
    {
        return Sale::create([
            'customer_type' => 'client_agent',
            'customer_id' => $client->id,
            'customer_name' => $client->name,
            'sale_date' => now()->format('Y-m-d'),
            'subtotal' => $total,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
        ]);
    }
}
