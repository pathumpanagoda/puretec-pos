<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add tenant_id to users table for multi-tenant awareness.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->index('tenant_id');

            if (DB::getDriverName() === 'mysql') {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['tenant_id']);
            }
            $table->dropColumn('tenant_id');
        });
    }
};
