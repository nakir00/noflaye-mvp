<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create scopes table
 *
 * Purpose: Unified scope management for permissions, roles, and templates
 * Replaces: scope_type/scope_id pairs with single scope_id reference
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
        Schema::create('scopes', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship
            $table->string('scopable_type', 255)->index()->comment('Morph type: App\\Models\\Shop, Kitchen, etc.');
            $table->unsignedBigInteger('scopable_id')->index()->comment('ID of the scoped entity');

            // Human-readable key
            $table->string('scope_key', 100)->unique()->comment('Format: type:id (e.g., shop:1, kitchen:5)');

            // Metadata
            $table->string('name', 255)->nullable()->comment('Display name for UI');
            $table->boolean('is_active')->default(true)->index()->comment('Soft activation toggle');

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for performance
            $table->index(['scopable_type', 'scopable_id'], 'idx_scopes_scopable');
            $table->index(['is_active', 'scopable_type'], 'idx_scopes_active_type');

            // Unique constraint to prevent duplicates
            $table->unique(['scopable_type', 'scopable_id'], 'unique_scopable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scopes');
    }
};
