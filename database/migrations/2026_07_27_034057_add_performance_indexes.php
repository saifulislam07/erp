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
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('status');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('movement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['movement_type']);
        });
    }
};
