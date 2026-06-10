<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the tenant_subscriptions table.
     * Tracks monthly subscription billing for each tenant.
     */
    public function up(): void
    {
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');

            // Billing period
            $table->string('billing_month', 7); // Format: "2026-03"
            $table->decimal('amount', 10, 2);
            $table->date('due_date');

            // Payment status
            $table->enum('status', ['pending', 'paid', 'overdue', 'locked'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Each tenant can only have one subscription per billing month
            $table->unique(['tenant_id', 'billing_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
