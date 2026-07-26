<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'store_dispatch_log_id',
        'delivery_person_name',
        'delivery_date',
        'status',
        'delivery_note',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function dispatchLog(): BelongsTo
    {
        return $this->belongsTo(StoreDispatchLog::class, 'store_dispatch_log_id');
    }
}
