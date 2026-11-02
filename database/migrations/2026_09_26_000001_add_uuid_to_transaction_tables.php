<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add UUID columns to transaction tables for offline sync idempotency.
     * Add device tracking and sync status columns.
     */
    public function up(): void
    {
        // Orders: UUID + device + sync tracking
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('device_id', 64)->nullable()->after('uuid');
            $table->timestamp('synced_at')->nullable()->after('completed_at');
            $table->enum('sync_status', ['local', 'synced', 'conflict'])->default('synced')->after('synced_at');
            $table->string('source', 20)->default('web')->after('sync_status'); // web, pwa, mobile
        });

        // Backfill existing orders with UUIDs
        DB::table('orders')->whereNull('uuid')->orderBy('id')->each(function ($order) {
            DB::table('orders')->where('id', $order->id)->update(['uuid' => Str::uuid()->toString()]);
        });

        // Now make UUID unique and not-null
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });

        // Payments: UUID
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('payments')->whereNull('uuid')->orderBy('id')->each(function ($payment) {
            DB::table('payments')->where('id', $payment->id)->update(['uuid' => Str::uuid()->toString()]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });

        // Inventory Movements: UUID
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('inventory_movements')->whereNull('uuid')->orderBy('id')->each(function ($movement) {
            DB::table('inventory_movements')->where('id', $movement->id)->update(['uuid' => Str::uuid()->toString()]);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'device_id', 'synced_at', 'sync_status', 'source']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
