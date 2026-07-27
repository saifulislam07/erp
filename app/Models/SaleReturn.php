<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'return_id',
        'sale_id',
        'return_date',
        'reason',
        'total_amount',
        'refund_amount',
        'refund_method',
        'restock',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'total_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'restock' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SaleReturn $return) {
            if (empty($return->return_id)) {
                $return->return_id = generateUniqueId(self::class, 'SR', 'return_id');
            }
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Value credited against the customer's balance rather than refunded in
     * cash at the counter.
     */
    public function getCreditedAmountAttribute(): float
    {
        return round((float) $this->total_amount - (float) $this->refund_amount, 2);
    }
}
