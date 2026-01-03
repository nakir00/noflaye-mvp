<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create permission_wildcards table
 *
 * Purpose: Wildcard permission patterns (e.g., shops.*, *.read)
 * Features: Pattern types, auto-expansion, caching
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permission_wildcards', function (Blueprint $table) {
            $table->id();

            // Pattern definition
            $table->string('pattern', 255)->unique()->comment('Wildcard pattern: shops.*, *.read, *.*');
            $table->text('description')->nullable()->comment('Pattern explanation');

            // Pattern types
            $table->enum('pattern_type', ['full', 'resource', 'action', 'macro'])
                ->default('full')
                ->comment('full=*.*, resource=shops.*, action=*.read, macro=shops.write');

            // UI configuration
            $table->string('icon', 100)->nullable()->comment('Icon for UI display');
            $table->string('color', 50)->default('primary')->comment('Badge color');
            $table->integer('sort_order')->default(0)->index()->comment('Display order');

            // State and expansion
            $table->boolean('is_active')->default(true)->index()->comment('Enable/disable wildcard');
            $table->boolean('auto_expand')->default(true)->comment('Auto-expand to permissions');

            // Cache metadata
            $table->timestamp('last_expanded_at')->nullable()->comment('Last expansion timestamp');
            $table->integer('permissions_count')->default(0)->comment('Cached count of matched permissions');

            $table->timestamps();

            // Performance indexes
            $table->index(['is_active', 'auto_expand'], 'idx_wildcards_active_expand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_wildcards');
    }
};
