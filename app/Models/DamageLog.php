<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'product_id',
        'quantity',
        'reason',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'logged_at' => 'datetime',
        ];
    }

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
