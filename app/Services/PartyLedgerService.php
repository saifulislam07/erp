<?php

namespace App\Services;

use App\Models\Client;
use App\Models\PartyPayment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Answers "how much does this supplier still get?" and "how much does this
 * customer still owe?", and records the payments that settle those balances.
 *
 * Balance definition
 * ------------------
 *   supplier balance = billed − returned − paid
 *   customer balance = billed − returned − received + refunded
 *
 * The refund term matters: goods coming back always reduce the debt, but if
 * cash was handed over the counter at the same time that reduction has already
 * been settled and must not be counted twice.
 *
 * `purchases.paid_amount` / `sales.paid_amount` stay the single source of truth
 * for what has been settled: a payment writes into them, and the model's saving
 * hook recomputes `due_amount` and `payment_status`. That keeps the invoice
 * screens, the reports and this ledger agreeing with each other.
 */
class PartyLedgerService
{
    public function __construct(private readonly CashBankService $cashBank) {}

    /* ------------------------------------------------------------ suppliers */

    /**
     * Every supplier with their outstanding balance, heaviest debt first.
     *
     * @return Collection<int, object>
     */
    public function supplierBalances(?string $search = null): Collection
    {
        $suppliers = Supplier::query()
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->get();

        $billed = DB::table('purchases')->whereNull('deleted_at')
            ->groupBy('supplier_id')
            ->selectRaw('supplier_id, SUM(total_amount) as total')
            ->pluck('total', 'supplier_id');

        $paid = DB::table('purchases')->whereNull('deleted_at')
            ->groupBy('supplier_id')
            ->selectRaw('supplier_id, SUM(paid_amount) as total')
            ->pluck('total', 'supplier_id');

        $returned = DB::table('purchase_returns')
            ->join('purchases', 'purchases.id', '=', 'purchase_returns.purchase_id')
            ->whereNull('purchase_returns.deleted_at')
            ->whereNull('purchases.deleted_at')
            ->groupBy('purchases.supplier_id')
            ->selectRaw('purchases.supplier_id as supplier_id, SUM(purchase_returns.total_amount) as total')
            ->pluck('total', 'supplier_id');

        $advances = $this->unallocatedByParty(PartyPayment::TYPE_SUPPLIER);

        return $suppliers->map(fn (Supplier $supplier) => (object) [
            'party' => $supplier,
            'billed' => (float) ($billed[$supplier->id] ?? 0),
            'returned' => (float) ($returned[$supplier->id] ?? 0),
            'paid' => (float) ($paid[$supplier->id] ?? 0),
            'advance' => (float) ($advances[$supplier->id] ?? 0),
            'balance' => round(
                (float) ($billed[$supplier->id] ?? 0)
                - (float) ($returned[$supplier->id] ?? 0)
                - (float) ($paid[$supplier->id] ?? 0),
                2
            ),
        ])->sortByDesc('balance')->values();
    }

    /* ------------------------------------------------------------ customers */

    /**
     * Every customer with money outstanding on their invoices.
     *
     * @return Collection<int, object>
     */
    public function customerBalances(?string $search = null): Collection
    {
        $clients = Client::query()
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->get();

        $billed = DB::table('sales')->whereNull('deleted_at')
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(total_amount) as total')
            ->pluck('total', 'customer_id');

        $received = DB::table('sales')->whereNull('deleted_at')
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(paid_amount) as total')
            ->pluck('total', 'customer_id');

        // Returned goods reduce the debt; money handed back at the counter
        // puts it straight back — hence the two separate sums.
        $returned = DB::table('sale_returns')
            ->join('sales', 'sales.id', '=', 'sale_returns.sale_id')
            ->whereNull('sale_returns.deleted_at')
            ->whereNull('sales.deleted_at')
            ->whereNotNull('sales.customer_id')
            ->groupBy('sales.customer_id')
            ->selectRaw('sales.customer_id as customer_id, SUM(sale_returns.total_amount) as total, SUM(sale_returns.refund_amount) as refunded')
            ->get()
            ->keyBy('customer_id');

        $advances = $this->unallocatedByParty(PartyPayment::TYPE_CLIENT);

        return $clients->map(function (Client $client) use ($billed, $received, $returned, $advances) {
            $row = $returned[$client->id] ?? null;

            return (object) [
                'party' => $client,
                'billed' => (float) ($billed[$client->id] ?? 0),
                'returned' => (float) ($row->total ?? 0),
                'paid' => (float) ($received[$client->id] ?? 0),
                'advance' => (float) ($advances[$client->id] ?? 0),
                'balance' => round(
                    (float) ($billed[$client->id] ?? 0)
                    - (float) ($row->total ?? 0)
                    - (float) ($received[$client->id] ?? 0)
                    + (float) ($row->refunded ?? 0),
                    2
                ),
            ];
        })->sortByDesc('balance')->values();
    }

    /* -------------------------------------------------------------- ledgers */

    /**
     * Invoices with money still outstanding, oldest first — the order a
     * payment is applied in.
     *
     * @return EloquentCollection<int, Purchase|Sale>
     */
    public function openInvoices(string $partyType, int $partyId): EloquentCollection
    {
        return $partyType === PartyPayment::TYPE_SUPPLIER
            ? Purchase::where('supplier_id', $partyId)
                ->where('due_amount', '>', 0)
                ->orderBy('purchase_date')
                ->orderBy('id')
                ->get()
            : Sale::where('customer_id', $partyId)
                ->where('due_amount', '>', 0)
                ->orderBy('sale_date')
                ->orderBy('id')
                ->get();
    }

    /**
     * All invoices for a party, newest first, for the statement view.
     *
     * @return EloquentCollection<int, Purchase|Sale>
     */
    public function invoices(string $partyType, int $partyId): EloquentCollection
    {
        return $partyType === PartyPayment::TYPE_SUPPLIER
            ? Purchase::with('returns')->where('supplier_id', $partyId)->latest('purchase_date')->latest('id')->get()
            : Sale::where('customer_id', $partyId)->latest('sale_date')->latest('id')->get();
    }

    /**
     * Payment history for a party, newest first.
     *
     * @return EloquentCollection<int, PartyPayment>
     */
    public function payments(string $partyType, int $partyId): EloquentCollection
    {
        return PartyPayment::with(['allocations', 'creator'])
            ->where('party_type', $partyType)
            ->where('party_id', $partyId)
            ->latest('payment_date')
            ->latest('id')
            ->get();
    }

    /**
     * Outstanding balance for a single party.
     */
    public function balanceFor(string $partyType, int $partyId): float
    {
        if ($partyType === PartyPayment::TYPE_SUPPLIER) {
            $billed = (float) Purchase::where('supplier_id', $partyId)->sum('total_amount');
            $paid = (float) Purchase::where('supplier_id', $partyId)->sum('paid_amount');
            $returned = (float) PurchaseReturn::whereHas(
                'purchase',
                fn ($q) => $q->where('supplier_id', $partyId)
            )->sum('total_amount');

            return round($billed - $returned - $paid, 2);
        }

        $billed = (float) Sale::where('customer_id', $partyId)->sum('total_amount');
        $received = (float) Sale::where('customer_id', $partyId)->sum('paid_amount');

        $returns = SaleReturn::whereHas('sale', fn ($q) => $q->where('customer_id', $partyId))
            ->selectRaw('COALESCE(SUM(total_amount), 0) as returned, COALESCE(SUM(refund_amount), 0) as refunded')
            ->first();

        return round($billed - (float) $returns->returned - $received + (float) $returns->refunded, 2);
    }

    /* ------------------------------------------------------------- payments */

    /**
     * Record a payment and apply it to the party's open invoices, oldest
     * first. Anything left over is kept as an unallocated advance.
     *
     * @param  array{party_type: string, party_id: int, payment_date: string, amount: float|string, method: string, reference?: ?string, note?: ?string}  $data
     */
    public function recordPayment(array $data, int $userId): PartyPayment
    {
        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw new RuntimeException('A payment must be greater than zero.');
        }

        return DB::transaction(function () use ($data, $amount, $userId) {
            $payment = PartyPayment::create([
                'party_type' => $data['party_type'],
                'party_id' => $data['party_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $userId,
            ]);

            $this->allocate($payment, $amount);
            $this->postToCashBank($payment, reverse: false, userId: $userId);

            return $payment;
        });
    }

    /**
     * Undo a payment: put the money back on each invoice it settled and
     * reverse the cash/bank movement.
     */
    public function deletePayment(PartyPayment $payment, int $userId): void
    {
        DB::transaction(function () use ($payment, $userId) {
            foreach ($payment->allocations as $allocation) {
                /** @var Purchase|Sale|null $invoice */
                $invoice = $allocation->invoice_type::find($allocation->invoice_id);

                if ($invoice) {
                    // Never drive paid_amount negative, even if the invoice was
                    // edited down after the payment was taken.
                    $invoice->paid_amount = max(0, (float) $invoice->paid_amount - (float) $allocation->amount);
                    $invoice->save();
                }
            }

            $this->postToCashBank($payment, reverse: true, userId: $userId);

            $payment->allocations()->delete();
            $payment->delete();
        });
    }

    /**
     * Apply a payment across the party's open invoices, oldest first.
     */
    private function allocate(PartyPayment $payment, float $amount): void
    {
        $remaining = $amount;

        foreach ($this->openInvoices($payment->party_type, $payment->party_id) as $invoice) {
            if ($remaining <= 0) {
                break;
            }

            $applied = min($remaining, (float) $invoice->due_amount);

            if ($applied <= 0) {
                continue;
            }

            $payment->allocations()->create([
                'invoice_type' => $invoice::class,
                'invoice_id' => $invoice->id,
                'amount' => $applied,
            ]);

            $invoice->paid_amount = (float) $invoice->paid_amount + $applied;
            $invoice->save();

            $remaining = round($remaining - $applied, 2);
        }
    }

    /**
     * Mirror the payment in the cash/bank ledger.
     *
     * Paying a supplier takes money out; receiving from a customer puts money
     * in. `$reverse` flips both for a deletion.
     */
    private function postToCashBank(PartyPayment $payment, bool $reverse, int $userId): void
    {
        $isOutgoing = $payment->party_type === PartyPayment::TYPE_SUPPLIER;
        $shouldDebit = $reverse ? ! $isOutgoing : $isOutgoing;

        $party = $payment->party();
        $label = $party?->name ?? 'party';
        $description = $reverse
            ? "Reversal of {$payment->payment_id} ({$label})"
            : ($isOutgoing ? "Payment {$payment->payment_id} to {$label}" : "Receipt {$payment->payment_id} from {$label}");

        $arguments = [
            'amount' => (float) $payment->amount,
            'method' => $payment->method,
            'referenceType' => PartyPayment::class,
            'referenceId' => $payment->id,
            'description' => $description,
            'userId' => $userId,
        ];

        $shouldDebit
            ? $this->cashBank->debit(...$arguments)
            : $this->cashBank->credit(...$arguments);
    }

    /**
     * Advances (payments not tied to an invoice) keyed by party id.
     *
     * @return Collection<int, float>
     */
    private function unallocatedByParty(string $partyType): Collection
    {
        return PartyPayment::query()
            ->where('party_type', $partyType)
            ->leftJoin('party_payment_allocations', 'party_payment_allocations.party_payment_id', '=', 'party_payments.id')
            ->whereNull('party_payments.deleted_at')
            ->groupBy('party_payments.id', 'party_payments.party_id', 'party_payments.amount')
            ->get([
                'party_payments.party_id',
                'party_payments.amount',
                DB::raw('COALESCE(SUM(party_payment_allocations.amount), 0) as allocated'),
            ])
            ->groupBy('party_id')
            ->map(fn ($rows) => round(
                $rows->sum(fn ($row) => (float) $row->amount - (float) $row->allocated),
                2
            ));
    }
}
