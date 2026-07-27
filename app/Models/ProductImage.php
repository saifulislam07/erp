<?php

namespace App\Models;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Removing a row must also remove the files it points at, whether the
        // delete came from the gallery editor or a cascading product delete.
        static::deleting(function (ProductImage $image) {
            app(MediaService::class)->delete($image->path);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return app(MediaService::class)->url($this->path);
    }

    public function getThumbUrlAttribute(): string
    {
        return app(MediaService::class)->url($this->path, thumb: true);
    }
}
