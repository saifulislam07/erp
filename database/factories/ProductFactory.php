<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'name' => fake()->unique()->words(3, true),
            'mrp_price' => 120,
            'purchase_price' => 80,
            'sale_price' => 100,
            'vat_percentage' => 0,
            'min_stock_threshold' => 0,
            'status' => true,
        ];
    }

    /**
     * A product that charges VAT, for the tax maths in purchases and sales.
     */
    public function withVat(float $percentage = 15): static
    {
        return $this->state(fn (array $attributes) => [
            'vat_percentage' => $percentage,
        ]);
    }

    /**
     * A product that warns once stock drops below the given level. The default
     * factory state disables the warning so unrelated tests are not noisy.
     */
    public function lowStockBelow(float $threshold): static
    {
        return $this->state(fn (array $attributes) => [
            'min_stock_threshold' => $threshold,
        ]);
    }
}
