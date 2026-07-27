<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'applicable_to',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * True when the discount is enabled and today falls inside its window.
     * An empty end date means the discount runs indefinitely.
     */
    public function isActive(?string $audience = null): bool
    {
        if (! $this->status) {
            return false;
        }

        if ($audience !== null && $this->applicable_to !== 'all' && $this->applicable_to !== $audience) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->start_date && $today->lt($this->start_date->startOfDay())) {
            return false;
        }

        return ! ($this->end_date && $today->gt($this->end_date->endOfDay()));
    }

    /**
     * Apply this discount to a price. Never returns a negative price — a fixed
     * discount larger than the price floors at zero.
     */
    public function applyTo(float $price): float
    {
        $off = $this->discount_type === 'percentage'
            ? $price * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return round(max(0, $price - $off), 2);
    }

    /**
     * Human-readable amount, e.g. "10%" or "BDT 50.00".
     */
    public function getLabelAttribute(): string
    {
        return $this->discount_type === 'percentage'
            ? rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'%'
            : money($this->discount_value);
    }

    /**
     * Window described in words, e.g. "01 Aug 2026 → ongoing".
     */
    public function getPeriodAttribute(): string
    {
        return $this->start_date?->format('d M Y').' → '.($this->end_date?->format('d M Y') ?? 'ongoing');
    }
}
