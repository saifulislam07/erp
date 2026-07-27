<?php

namespace App\Http\Controllers\Admin;

use App\Models\PartyPayment;

/**
 * Accounts receivable: what each customer still owes, and collecting it.
 */
class CustomerPaymentController extends PartyPaymentController
{
    protected function partyType(): string
    {
        return PartyPayment::TYPE_CLIENT;
    }

    protected function routePrefix(): string
    {
        return 'admin.customer-payments';
    }

    protected function labels(): array
    {
        return [
            'title' => 'Customer dues',
            'party' => 'Customer',
            'parties' => 'Customers',
            'balance' => 'Owes us',
            'billed' => 'Invoiced',
            'paid' => 'Received',
            'invoices' => 'Sales',
            'invoice' => 'Sale',
            'payments' => 'Payments received',
            'action' => 'Record receipt',
            'recorded' => 'Receipt recorded as',
            'settled' => 'Fully settled',
            'direction' => 'Money into cash/bank.',
            'empty' => 'No customers yet.',
            'nothing_due' => 'No customer owes anything.',
        ];
    }
}
