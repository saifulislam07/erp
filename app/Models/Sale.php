<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'sale_id',
        'customer_type',
        'customer_id',
        'customer_name',
        'sale_date',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'paid_amount',
        'due_amount',
        'transaction_reference',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (empty($sale->sale_id)) {
                $sale->sale_id = generateUniqueId(self::class, '', 'sale_id');
            }
        });

        static::saving(function (Sale $sale) {
            $sale->due_amount = $sale->total_amount - $sale->paid_amount;

            $sale->payment_status = match (true) {
                $sale->due_amount <= 0 => 'paid',
                $sale->paid_amount > 0 => 'partial',
                default => 'unpaid',
            };
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
