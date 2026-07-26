<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'from_method',
        'to_method',
        'amount',
        'transfer_date',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transfer_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CashTransfer $transfer) {
            if (empty($transfer->transfer_id)) {
                $transfer->transfer_id = generateUniqueId(self::class, '', 'transfer_id');
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
