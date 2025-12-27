<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create permission_templates table
 *
 * Purpose: Permission templates for role-based permission bundles
 * Features: Hierarchical structure, scope support, auto-sync capabilities
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permission_templates', function (Blueprint $table) {
            $table->id();

            // Identifiers
            $table->string('name', 255)->index()->comment('Template display name');
            $table->string('slug', 255)->unique()->comment('Unique template identifier');
            $table->text('description')->nullable()->comment('Template purpose and usage');

            // Hierarchical structure
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent template for inheritance');
            $table->foreign('parent_id')->references('id')->on('permission_templates')
                ->onDelete('set null')->onUpdate('cascade');

            // Default scope
            $table->unsignedBigInteger('scope_id')->nullable()->comment('Default scope for this template');
            $table->foreign('scope_id')->references('id')->on('scopes')
                ->onDelete('set null')->onUpdate('cascade');

            // UI configuration
            $table->string('color', 50)->default('primary')->comment('Badge color for Filament UI');
            $table->string('icon', 100)->default('heroicon-o-shield-check')->comment('Icon for Filament UI');
            $table->integer('level')->default(0)->comment('Calculated hierarchy level (0=root)');
            $table->integer('sort_order')->default(0)->index()->comment('Display order in UI');

            // State flags
            $table->boolean('is_active')->default(true)->index()->comment('Enable/disable template');
            $table->boolean('is_system')->default(false)->comment('System template (cannot delete)');
            $table->boolean('auto_sync_users')->default(true)->comment('Auto-sync permissions to users');

            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['is_active', 'is_system'], 'idx_templates_active_system');
            $table->index(['level', 'sort_order'], 'idx_templates_hierarchy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_templates');
    }
};
