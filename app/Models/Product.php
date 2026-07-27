<?php

namespace App\Models;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'unique_id',
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'description',
        'unit_id',
        'mrp_price',
        'purchase_price',
        'sale_price',
        'vat_percentage',
        'min_stock_threshold',
        'image',
        'status',
        'expire_alert_1month',
        'expire_alert_3month',
    ];

    protected function casts(): array
    {
        return [
            'mrp_price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'vat_percentage' => 'decimal:2',
            'min_stock_threshold' => 'decimal:2',
            'status' => 'boolean',
            'expire_alert_1month' => 'boolean',
            'expire_alert_3month' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->unique_id)) {
                $product->unique_id = generateUniqueId(self::class, 'PRD', 'unique_id');
            }

            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::random(5);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Gallery images, primary first, then in the order the user arranged them.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ProductDiscount::class);
    }

    /**
     * Discount that applies right now, if any. A product may have several
     * scheduled discounts; only one window can be current.
     */
    public function activeDiscount(): ?ProductDiscount
    {
        return $this->discounts
            ->first(fn (ProductDiscount $discount) => $discount->isActive());
    }

    /**
     * Sale price after any discount that is running today.
     */
    public function getEffectivePriceAttribute(): float
    {
        $discount = $this->activeDiscount();

        return $discount
            ? $discount->applyTo((float) $this->sale_price)
            : (float) $this->sale_price;
    }

    /**
     * Amount taken off the sale price by the running discount.
     */
    public function getDiscountAmountAttribute(): float
    {
        return round((float) $this->sale_price - $this->effective_price, 2);
    }

    /**
     * Gross profit per unit at the current selling price.
     */
    public function getMarginAttribute(): float
    {
        return round($this->effective_price - (float) $this->purchase_price, 2);
    }

    /**
     * Margin as a percentage of the selling price.
     */
    public function getMarginPercentageAttribute(): float
    {
        $price = $this->effective_price;

        return $price > 0 ? round($this->margin / $price * 100, 2) : 0.0;
    }

    /**
     * Public URL of the product's primary image (or the placeholder).
     */
    public function imageUrl(bool $thumb = false): string
    {
        return app(MediaService::class)->url($this->image, $thumb);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
