<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A payment made to a supplier or received from a customer.
 *
 * @property-read Collection<int, PartyPaymentAllocation> $allocations
 */
class PartyPayment extends Model
{
    use SoftDeletes;

    public const TYPE_SUPPLIER = 'supplier';

    public const TYPE_CLIENT = 'client';

    protected $fillable = [
        'payment_id',
        'party_type',
        'party_id',
        'payment_date',
        'amount',
        'method',
        'reference',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PartyPayment $payment) {
            if (empty($payment->payment_id)) {
                $prefix = $payment->party_type === self::TYPE_SUPPLIER ? 'PAY' : 'RCV';
                $payment->payment_id = generateUniqueId(self::class, $prefix, 'payment_id');
            }
        });
    }

    public function scopeSupplierPayments(Builder $query): Builder
    {
        return $query->where('party_type', self::TYPE_SUPPLIER);
    }

    public function scopeCustomerReceipts(Builder $query): Builder
    {
        return $query->where('party_type', self::TYPE_CLIENT);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PartyPaymentAllocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The supplier or client this payment belongs to.
     *
     * Not a relation: `party_type` stores a domain label ('supplier'/'client')
     * rather than a class name, so the two sides are resolved explicitly.
     */
    public function party(): Supplier|Client|null
    {
        return $this->party_type === self::TYPE_SUPPLIER
            ? Supplier::find($this->party_id)
            : Client::find($this->party_id);
    }

    /**
     * Portion of this payment not tied to a specific invoice — an advance.
     */
    public function getUnallocatedAmountAttribute(): float
    {
        return round((float) $this->amount - (float) $this->allocations->sum('amount'), 2);
    }

    public function getDirectionAttribute(): string
    {
        return $this->party_type === self::TYPE_SUPPLIER ? 'out' : 'in';
    }
}
