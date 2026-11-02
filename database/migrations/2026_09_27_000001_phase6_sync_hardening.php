<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Phase 6: Sync Hardening & Idempotency
     *
     * - Add sync_version for incremental sync cursor
     * - Add composite indexes for sync performance
     * - Add order_items UUID for idempotency
     * - Ensure DB-level unique constraints on all transaction UUIDs
     * - Add stock movement type index
     */
    public function up(): void
    {
        // Add sync_version to products for cursor-based incremental sync
        if (!Schema::hasColumn('products', 'sync_version')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('sync_version')->default(0)->after('updated_at');
            });
        }

        // Add sync_version to customers
        if (!Schema::hasColumn('customers', 'sync_version')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedBigInteger('sync_version')->default(0)->after('updated_at');
            });
        }

        // Add sync_version to categories
        if (!Schema::hasColumn('categories', 'sync_version')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('sync_version')->default(0)->after('updated_at');
            });
        }

        // Add UUID to order_items for fine-grained idempotency
        if (!Schema::hasColumn('order_items', 'uuid')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });

            // Backfill
            DB::table('order_items')->whereNull('uuid')->orderBy('id')->each(function ($item) {
                DB::table('order_items')->where('id', $item->id)->update(['uuid' => \Illuminate\Support\Str::uuid()->toString()]);
            });

            Schema::table('order_items', function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->unique()->change();
            });
        }

        // Add composite indexes for sync performance on inventory_movements
        if (!Schema::hasColumn('inventory_movements', 'reference_uuid')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->string('reference_uuid', 36)->nullable()->after('reference_id');
                $table->index(['store_id', 'type', 'created_at'], 'inv_mov_store_type_created');
                $table->index(['product_id', 'created_at'], 'inv_mov_product_created');
            });
        }

        // Add sync tracking columns to orders
        if (!Schema::hasColumn('orders', 'sync_attempts')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedTinyInteger('sync_attempts')->default(0)->after('sync_status');
                $table->text('sync_error')->nullable()->after('sync_attempts');
            });
        }

        // Create sync_versions table for global sync cursor tracking
        if (!Schema::hasTable('sync_versions')) {
            Schema::create('sync_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->string('entity_type', 50); // products, customers, categories
                $table->unsignedBigInteger('last_version')->default(0);
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamps();

                $table->unique(['store_id', 'entity_type']);
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sync_version');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('sync_version');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sync_version');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn('reference_uuid');
            $table->dropIndex('inv_mov_store_type_created');
            $table->dropIndex('inv_mov_product_created');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['sync_attempts', 'sync_error']);
        });
        Schema::dropIfExists('sync_versions');
    }
};
