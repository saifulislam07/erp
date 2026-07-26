<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Packaging extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'packed_by',
        'packed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'packed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }
}
