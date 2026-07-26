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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->string('category')->nullable();
            $table->decimal('purchase_price', 10, 2);
            $table->date('purchase_date');
            $table->date('warranty_until')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('place_of_purchase')->nullable();
            $table->integer('quantity')->default(1);
            $table->text('description')->nullable();
            $table->string('supplier_name')->nullable();
            $table->text('supplier_address')->nullable();
            $table->string('invoice_file')->nullable();
            $table->enum('status', ['active', 'disposed', 'lost'])->default('active');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
