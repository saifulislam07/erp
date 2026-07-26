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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('store_dispatch_log_id')->constrained('store_dispatch_logs')->cascadeOnDelete();
            $table->string('delivery_person_name')->nullable();
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['pending', 'out_for_delivery', 'delivered', 'failed'])->default('pending');
            $table->text('delivery_note')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
