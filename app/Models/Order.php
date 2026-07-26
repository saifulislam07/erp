<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'client_id',
        'status',
        'payment_method',
        'transaction_reference',
        'payment_receipt',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total_amount',
        'delivery_charge',
        'note',
        'admin_note',
        'cancel_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_id)) {
                $order->order_id = generateUniqueId(self::class, '', 'order_id');
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at');
    }

    public function packaging(): HasOne
    {
        return $this->hasOne(Packaging::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function dispatchLog(): HasOne
    {
        return $this->hasOne(StoreDispatchLog::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function logStatusChange(?string $from, string $to, string $changedByType, int $changedById, ?string $note = null): OrderStatusLog
    {
        return $this->statusLogs()->create([
            'from_status' => $from,
            'to_status' => $to,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'note' => $note,
        ]);
    }
}
