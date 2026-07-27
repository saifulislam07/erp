<?php

namespace App\Http\Controllers\Admin;

use App\Models\PartyPayment;

/**
 * Accounts payable: what each supplier is still owed, and paying them.
 */
class SupplierPaymentController extends PartyPaymentController
{
    protected function partyType(): string
    {
        return PartyPayment::TYPE_SUPPLIER;
    }

    protected function routePrefix(): string
    {
        return 'admin.supplier-payments';
    }

    protected function labels(): array
    {
        return [
            'title' => 'Supplier dues',
            'party' => 'Supplier',
            'parties' => 'Suppliers',
            'balance' => 'We owe',
            'billed' => 'Purchased',
            'paid' => 'Paid',
            'invoices' => 'Purchases',
            'invoice' => 'Purchase',
            'payments' => 'Payments made',
            'action' => 'Record payment',
            'recorded' => 'Payment recorded as',
            'settled' => 'Fully paid',
            'direction' => 'Money out of cash/bank.',
            'empty' => 'No suppliers yet.',
            'nothing_due' => 'Nothing is owed to any supplier.',
        ];
    }
}
