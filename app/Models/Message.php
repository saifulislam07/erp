<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_type',
        'sender_id',
        'receiver_type',
        'receiver_id',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where(function ($q) use ($clientId) {
            $q->where('sender_type', 'client')->where('sender_id', $clientId);
        })->orWhere(function ($q) use ($clientId) {
            $q->where('receiver_type', 'client')->where('receiver_id', $clientId);
        });
    }
}
