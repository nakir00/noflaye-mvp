<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create template_permissions table
 *
 * Purpose: Link templates to permissions with source tracking
 * Features: Direct, wildcard, and inherited permission tracking
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
        Schema::create('template_permissions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('template_id')->comment('Permission template ID');
            $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');

            $table->unsignedBigInteger('permission_id')->comment('Permission ID');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            // Source tracking
            $table->enum('source', ['direct', 'wildcard', 'inherited'])
                ->default('direct')
                ->comment('direct=manually added, wildcard=from pattern, inherited=from parent');

            $table->unsignedBigInteger('wildcard_id')->nullable()->comment('Source wildcard if applicable');
            $table->foreign('wildcard_id')->references('id')->on('permission_wildcards')->onDelete('set null');

            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();

            // Unique constraint and indexes
            $table->unique(['template_id', 'permission_id'], 'unique_template_permission');
            $table->index(['source', 'wildcard_id'], 'idx_source_wildcard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_permissions');
    }
};
