<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_id',
        'order_id',
        'client_id',
        'return_type_id',
        'reason',
        'note',
        'status',
        'refund_amount',
        'refund_method',
        'requested_at',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderReturn $return) {
            if (empty($return->return_id)) {
                $return->return_id = generateUniqueId(self::class, '', 'return_id');
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function returnType(): BelongsTo
    {
        return $this->belongsTo(ReturnType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
