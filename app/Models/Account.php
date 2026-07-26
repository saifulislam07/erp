<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'party_type',
        'party_id',
        'amount',
        'due_date',
        'description',
        'is_settled',
        'settled_at',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'is_settled' => 'boolean',
            'settled_at' => 'datetime',
        ];
    }

    public function party(): BelongsTo
    {
        return $this->party_type === 'supplier'
            ? $this->belongsTo(Supplier::class, 'party_id')
            : $this->belongsTo(Client::class, 'party_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
