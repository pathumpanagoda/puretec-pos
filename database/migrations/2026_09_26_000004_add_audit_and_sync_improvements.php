<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add audit logging improvements and API token support.
     */
    public function up(): void
    {
        // Add personal_access_tokens for Sanctum API auth (mobile + sync)
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // Improve activity_logs table with additional fields for comprehensive audit
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('device_id', 64)->nullable()->after('ip_address');
            $table->string('user_agent')->nullable()->after('device_id');
            $table->string('severity', 20)->default('info')->after('description'); // info, warning, critical
        });

        // Add updated_at index to key tables for pull sync
        // (check if products table exists and add sync tracking)
        Schema::table('products', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->index('updated_at');
        });

        // Backfill product UUIDs
        \Illuminate\Support\Facades\DB::table('products')->whereNull('uuid')->orderBy('id')->each(function ($product) {
            \Illuminate\Support\Facades\DB::table('products')->where('id', $product->id)->update(['uuid' => \Illuminate\Support\Str::uuid()->toString()]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });

        // Add UUID to customers for sync
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->index('updated_at');
        });

        \Illuminate\Support\Facades\DB::table('customers')->whereNull('uuid')->orderBy('id')->each(function ($customer) {
            \Illuminate\Support\Facades\DB::table('customers')->where('id', $customer->id)->update(['uuid' => \Illuminate\Support\Str::uuid()->toString()]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });

        // Add UUID to categories for sync
        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        \Illuminate\Support\Facades\DB::table('categories')->whereNull('uuid')->orderBy('id')->each(function ($cat) {
            \Illuminate\Support\Facades\DB::table('categories')->where('id', $cat->id)->update(['uuid' => \Illuminate\Support\Str::uuid()->toString()]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'user_agent', 'severity']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
            $table->dropColumn('uuid');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
            $table->dropColumn('uuid');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::dropIfExists('personal_access_tokens');
    }
};
