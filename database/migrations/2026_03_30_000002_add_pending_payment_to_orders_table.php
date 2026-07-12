<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'pending_payment' to status enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','cancelled','refunded','on_hold','pending_payment') DEFAULT 'pending'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('billed_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('payment_processed_by')->nullable()->after('billed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('billed_at')->nullable()->after('completed_at');
            $table->timestamp('payment_approved_at')->nullable()->after('billed_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['billed_by']);
            $table->dropForeign(['payment_processed_by']);
            $table->dropColumn(['billed_by', 'payment_processed_by', 'billed_at', 'payment_approved_at']);
        });

        // Revert status enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','cancelled','refunded','on_hold') DEFAULT 'pending'");
        }
    }
};
