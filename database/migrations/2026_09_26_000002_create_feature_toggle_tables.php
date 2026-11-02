<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create client_features table for feature toggle system.
     * Also create feature_definitions as the master list.
     */
    public function up(): void
    {
        // Master feature definitions
        Schema::create('feature_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // e.g., 'sales', 'inventory', 'purchases'
            $table->string('name');                // e.g., 'Sales'
            $table->string('group', 50)->default('core'); // core, advanced, addon
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable(); // Bootstrap icon class
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(true); // enabled by default for new clients
            $table->boolean('is_core')->default(false);    // cannot be disabled
            $table->timestamps();
        });

        // Per-client feature toggles
        Schema::create('client_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('feature_code', 50);
            $table->boolean('is_enabled')->default(true);
            $table->json('config')->nullable(); // feature-specific configuration
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_code']);
            $table->index('feature_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_features');
        Schema::dropIfExists('feature_definitions');
    }
};
