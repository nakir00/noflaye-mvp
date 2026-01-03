<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create wildcard pivot tables
 *
 * Purpose: Link wildcards to permissions and templates
 * Tables: wildcard_permissions, template_wildcards
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
        // Wildcard to Permission expansion cache
        Schema::create('wildcard_permissions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('wildcard_id')->comment('Wildcard pattern ID');
            $table->foreign('wildcard_id')->references('id')->on('permission_wildcards')->onDelete('cascade');

            $table->unsignedBigInteger('permission_id')->comment('Matched permission ID');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->boolean('is_auto_generated')->default(true)->comment('Auto-generated vs manual');
            $table->timestamp('expanded_at')->useCurrent()->comment('Expansion timestamp');

            // Unique constraint and indexes
            $table->unique(['wildcard_id', 'permission_id'], 'unique_wildcard_permission');
            $table->index('wildcard_id');
            $table->index('permission_id');
        });

        // Template to Wildcard assignment
        Schema::create('template_wildcards', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('template_id')->comment('Permission template ID');
            $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');

            $table->unsignedBigInteger('wildcard_id')->comment('Wildcard pattern ID');
            $table->foreign('wildcard_id')->references('id')->on('permission_wildcards')->onDelete('cascade');

            $table->integer('sort_order')->default(0)->comment('Display order in template');
            $table->timestamps();

            // Unique constraint
            $table->unique(['template_id', 'wildcard_id'], 'unique_template_wildcard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_wildcards');
        Schema::dropIfExists('wildcard_permissions');
    }
};
