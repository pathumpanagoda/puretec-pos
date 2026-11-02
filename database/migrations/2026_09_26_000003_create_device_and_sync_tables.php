<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create device_registrations table for tracking POS devices.
     * Create sync_logs table for server-side sync tracking.
     */
    public function up(): void
    {
        // Device registrations — tracks all POS devices (web, mobile)
        Schema::create('device_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id', 64)->unique();       // Generated UUID per device
            $table->string('device_name')->nullable();        // "Chrome on Windows", "Flutter Android"
            $table->enum('device_type', ['web', 'pwa', 'mobile_android', 'mobile_ios'])->default('web');
            $table->string('app_version', 20)->nullable();
            $table->string('os_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->integer('pending_sync_count')->default(0);
            $table->json('capabilities')->nullable();         // What hardware is available
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // Server-side sync log — records every sync operation
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id', 64)->nullable();
            $table->enum('direction', ['push', 'pull'])->default('push');
            $table->string('entity_type', 50);               // 'order', 'payment', 'inventory_movement'
            $table->uuid('entity_uuid')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('status', ['success', 'duplicate', 'conflict', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->json('payload_summary')->nullable();      // Key fields for debugging (not full payload)
            $table->integer('batch_size')->default(1);
            $table->integer('duration_ms')->nullable();       // Processing time
            $table->timestamps();

            $table->index(['device_id', 'created_at']);
            $table->index(['entity_type', 'entity_uuid']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('device_registrations');
    }
};
