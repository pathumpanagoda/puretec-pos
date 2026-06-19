<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'pending_payment' to status enum — only needed for MySQL
        // SQLite stores enum as TEXT, so all values are already accepted
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','cancelled','refunded','on_hold','pending_payment') DEFAULT 'pending'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('billed_by')->nullable();
            $table->unsignedBigInteger('payment_processed_by')->nullable();
            $table->timestamp('billed_at')->nullable();
            $table->timestamp('payment_approved_at')->nullable();

            // Add foreign keys only for MySQL (SQLite doesn't support adding FK via ALTER)
            if (DB::getDriverName() === 'mysql') {
                $table->foreign('billed_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('payment_processed_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['billed_by']);
                $table->dropForeign(['payment_processed_by']);
            }
            $table->dropColumn(['billed_by', 'payment_processed_by', 'billed_at', 'payment_approved_at']);
        });

        // Revert status enum — only for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','cancelled','refunded','on_hold') DEFAULT 'pending'");
        }
    }
};
