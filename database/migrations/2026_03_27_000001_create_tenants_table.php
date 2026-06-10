<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the tenants table for multi-tenant SaaS system.
     * Each tenant represents a business using Ceyloan POS.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Unique tenant code e.g., "SHOP001"
            $table->string('business_name');
            $table->string('owner_name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();

            // Subscription details
            $table->enum('subscription_plan', ['basic', 'standard', 'premium'])->default('basic');
            $table->decimal('monthly_fee', 10, 2)->default(0);

            // Status flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->string('lock_reason')->nullable();

            // Trial period
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
