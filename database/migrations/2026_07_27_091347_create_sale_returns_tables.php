<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Goods coming back from a sale — the mirror of purchase_returns.
 *
 * The returned value always reduces what the customer owes. `refund_amount`
 * records the part of it handed straight back in cash instead, which is why
 * the customer balance is
 *
 *     billed − returned − received + refunded
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_id')->unique();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->date('return_date');
            $table->text('reason');
            $table->decimal('total_amount', 12, 2);

            // Money handed back at the counter, if any.
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->enum('refund_method', ['cash', 'bank', 'mobile_banking'])->nullable();

            // Damaged goods come back to the customer's hands, not to the shelf.
            $table->boolean('restock')->default(true);

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('return_date');
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained('sale_returns')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};
