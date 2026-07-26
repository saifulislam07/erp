<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashBankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'method',
        'amount',
        'reference_type',
        'reference_id',
        'description',
        'transaction_date',
        'created_by',
        'balance_cash_after',
        'balance_bank_after',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_cash_after' => 'decimal:2',
            'balance_bank_after' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
