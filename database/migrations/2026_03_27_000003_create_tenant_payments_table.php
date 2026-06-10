<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the tenant_payments table.
     * Records all payment transactions from tenants.
     */
    public function up(): void
    {
        Schema::create('tenant_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('tenant_subscriptions')->onDelete('set null');

            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_method'); // cash, bank_transfer, card, etc.
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('received_by')->nullable(); // Platform user who received payment
            $table->text('notes')->nullable();

            $table->timestamps();

            // Index for quick lookups
            $table->index(['tenant_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payments');
    }
};
