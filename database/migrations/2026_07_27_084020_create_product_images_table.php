<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A product now carries a gallery instead of a single image. The existing
 * `products.image` column is kept as the denormalised primary image so every
 * listing that already reads it keeps working without a join.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });

        // Carry existing single images into the gallery so nothing is lost.
        $existing = DB::table('products')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->get(['id', 'image', 'created_at']);

        foreach ($existing as $product) {
            DB::table('product_images')->insert([
                'product_id' => $product->id,
                'path' => $product->image,
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => $product->created_at ?? now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
