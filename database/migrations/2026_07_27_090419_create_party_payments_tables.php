<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Money paid to a supplier or received from a customer outside the moment the
 * invoice was raised.
 *
 * A payment is recorded once and then allocated across one or more open
 * invoices, so a single 50,000 transfer can clear three purchases. The
 * allocation rows are what make a payment reversible: deleting a payment walks
 * its allocations and puts the money back on each invoice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique();

            // 'supplier' → money out; 'client' → money in.
            $table->enum('party_type', ['supplier', 'client']);
            $table->unsignedBigInteger('party_id');

            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank', 'mobile_banking'])->default('cash');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['party_type', 'party_id']);
            $table->index('payment_date');
        });

        Schema::create('party_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_payment_id')->constrained('party_payments')->cascadeOnDelete();

            // Points at a Purchase (supplier payments) or a Sale (receipts).
            $table->string('invoice_type');
            $table->unsignedBigInteger('invoice_id');

            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['invoice_type', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_payment_allocations');
        Schema::dropIfExists('party_payments');
    }
};
