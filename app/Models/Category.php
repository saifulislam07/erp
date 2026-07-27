<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    private const TOP_LEVEL_CACHE_KEY = 'categories.top-level';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name).'-'.Str::random(5);
            }
        });

        static::saved(fn () => Cache::forget(self::TOP_LEVEL_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::TOP_LEVEL_CACHE_KEY));
    }

    /**
     * Cached list of top-level (parent) categories, ordered by name.
     */
    public static function topLevel(): Collection
    {
        $rows = Cache::remember(
            self::TOP_LEVEL_CACHE_KEY,
            3600,
            fn () => self::whereNull('parent_id')->orderBy('name')->get()->map->getAttributes()->all(),
        );

        return self::hydrate($rows);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
