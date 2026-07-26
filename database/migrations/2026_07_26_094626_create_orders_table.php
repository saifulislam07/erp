<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->enum('status', ['pending', 'processing', 'confirmed', 'on_delivery', 'delivered', 'rejected', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash_on_delivery', 'bank', 'mobile_banking']);
            $table->string('transaction_reference')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->foreignId('created_by')->constrained('clients')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
