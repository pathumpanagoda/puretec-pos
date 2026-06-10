<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('billing_contact_email')->nullable()->after('monthly_fee');
            $table->string('billing_contact_phone')->nullable()->after('billing_contact_email');
            $table->integer('payment_due_day')->default(1)->after('billing_contact_phone'); // Day of month
            $table->boolean('payment_reminder_enabled')->default(true)->after('payment_due_day');
            $table->date('next_payment_due')->nullable()->after('payment_reminder_enabled');
            $table->text('payment_notes')->nullable()->after('next_payment_due');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'billing_contact_email',
                'billing_contact_phone',
                'payment_due_day',
                'payment_reminder_enabled',
                'next_payment_due',
                'payment_notes',
            ]);
        });
    }
};
