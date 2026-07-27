<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * How much of a payment was applied to one specific invoice.
 */
class PartyPaymentAllocation extends Model
{
    protected $fillable = [
        'party_payment_id',
        'invoice_type',
        'invoice_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PartyPayment::class, 'party_payment_id');
    }

    /**
     * The Purchase or Sale this allocation settles.
     */
    public function invoice(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'invoice_type', 'invoice_id');
    }
}
