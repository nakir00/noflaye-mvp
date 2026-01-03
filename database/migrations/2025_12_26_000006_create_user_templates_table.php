<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create user_templates table
 *
 * Purpose: Assign permission templates to users with scope and versioning
 * Features: Auto-sync, validity periods, version tracking
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
        Schema::create('user_templates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->comment('User ID');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('template_id')->comment('Permission template ID');
            $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');

            // Scope
            $table->unsignedBigInteger('scope_id')->nullable()->comment('Scoped template assignment');
            $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');

            // Versioning
            $table->integer('template_version')->nullable()->comment('Locked version (null = latest)');
            $table->boolean('auto_upgrade')->default(true)->comment('Auto-upgrade to new versions');

            // Synchronization
            $table->boolean('auto_sync')->default(true)->comment('Auto-sync permissions from template');

            // Validity period
            $table->timestamp('valid_from')->nullable()->comment('Start date for template validity');
            $table->timestamp('valid_until')->nullable()->comment('End date for template validity');

            // Audit metadata
            $table->text('reason')->nullable()->comment('Assignment reason or justification');
            $table->unsignedBigInteger('granted_by')->nullable()->comment('User who granted this template');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Unique constraint and indexes
            $table->unique(['user_id', 'template_id', 'scope_id'], 'unique_user_template_scope');
            $table->index(['template_version', 'auto_upgrade'], 'idx_user_templates_versioning');
            $table->index(['valid_from', 'valid_until'], 'idx_user_templates_validity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_templates');
    }
};
