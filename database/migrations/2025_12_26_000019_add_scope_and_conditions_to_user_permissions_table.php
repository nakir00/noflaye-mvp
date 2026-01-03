<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add scope and conditions to user_permissions table
 *
 * Purpose: Add unified scope, conditions, and source tracking
 * Features: Replaces scope_type/scope_id with scope_id reference
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
        Schema::table('user_permissions', function (Blueprint $table) {
            // Unified scope reference
            $table->unsignedBigInteger('scope_id')->nullable()->after('permission_id')
                ->comment('Unified scope reference');
            $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');

            // Conditional permissions (JSON rules)
            $table->json('conditions')->nullable()->after('scope_id')
                ->comment('Permission conditions as JSON rules');

            // Source tracking
            $table->string('source', 50)->default('direct')->after('conditions')
                ->comment('direct, role, template, delegation, etc.');
            $table->unsignedBigInteger('source_id')->nullable()->after('source')
                ->comment('Source entity ID (role_id, template_id, etc.)');

            // Performance indexes
            $table->index('scope_id');
            $table->index(['source', 'source_id'], 'idx_user_permissions_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropForeign(['scope_id']);
            $table->dropIndex('idx_user_permissions_source');
            $table->dropIndex(['scope_id']);
            $table->dropColumn(['scope_id', 'conditions', 'source', 'source_id']);
        });
    }
};
