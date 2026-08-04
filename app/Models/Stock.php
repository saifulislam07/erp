<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'quantity',
        'expiry_date',
        'batch_number',
        'purchase_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    /**
     * How close this batch is to expiring: `expired`, `one_month`,
     * `three_month`, or `ok` when there is nothing to warn about. The stock
     * listing colours its rows from this, so the thresholds live here rather
     * than being re-derived in each view.
     */
    public function getExpiryStateAttribute(): string
    {
        if (! $this->expiry_date) {
            return 'ok';
        }

        if ($this->expiry_date->isPast()) {
            return 'expired';
        }

        $daysLeft = now()->startOfDay()->diffInDays($this->expiry_date, absolute: true);

        return match (true) {
            $daysLeft <= 30 => 'one_month',
            $daysLeft <= 90 => 'three_month',
            default => 'ok',
        };
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
