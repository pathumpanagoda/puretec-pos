<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->nullable()->after('email');
            $table->string('security_question')->nullable()->after('password');
            $table->string('security_answer')->nullable()->after('security_question');
            $table->timestamp('password_reset_at')->nullable();
        });

        Schema::create('platform_password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');  // email or username
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->index('identifier');
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_password_resets');

        Schema::table('platform_users', function (Blueprint $table) {
            $table->dropColumn(['username', 'security_question', 'security_answer', 'password_reset_at']);
        });
    }
};
