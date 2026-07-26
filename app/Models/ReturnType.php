<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'disposition',
        'description',
    ];

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }
}
