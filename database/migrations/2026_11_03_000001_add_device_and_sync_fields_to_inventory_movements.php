<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds device_id and sync_status to inventory_movements
     * to support offline stock movement tracking and multi-device synchronization.
     */
    public function up(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_movements', 'device_id')) {
                    $table->string('device_id', 64)->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('inventory_movements', 'sync_status')) {
                    $table->enum('sync_status', ['local', 'synced', 'conflict'])->default('synced')->after('device_id');
                }
                $table->index(['store_id', 'sync_status', 'created_at'], 'inv_mov_store_sync_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_movements', 'sync_status')) {
                    $table->dropIndex('inv_mov_store_sync_created');
                    $table->dropColumn('sync_status');
                }
                if (Schema::hasColumn('inventory_movements', 'device_id')) {
                    $table->dropColumn('device_id');
                }
            });
        }
    }
};

