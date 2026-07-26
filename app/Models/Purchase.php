<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'purchase_date',
        'invoice_number',
        'subtotal',
        'vat_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'subtotal' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Purchase $purchase) {
            if (empty($purchase->purchase_id)) {
                $purchase->purchase_id = generateUniqueId(self::class, '', 'purchase_id');
            }
        });

        static::saving(function (Purchase $purchase) {
            $purchase->due_amount = $purchase->total_amount - $purchase->paid_amount;

            $purchase->payment_status = match (true) {
                $purchase->due_amount <= 0 => 'paid',
                $purchase->paid_amount > 0 => 'partial',
                default => 'unpaid',
            };
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
