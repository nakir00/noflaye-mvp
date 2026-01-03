<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add hierarchy columns to user_groups table
 *
 * Purpose: Add parent_id, level, template support to user_groups
 * Features: Hierarchical groups, template auto-sync
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
        Schema::table('user_groups', function (Blueprint $table) {
            // Hierarchical structure
            $table->unsignedBigInteger('parent_id')->nullable()->after('id')->comment('Parent group ID');
            $table->foreign('parent_id')->references('id')->on('user_groups')
                ->onDelete('set null')->onUpdate('cascade');
            $table->integer('level')->default(0)->after('parent_id')->comment('Hierarchy level (0=root)');

            // Template association
            $table->unsignedBigInteger('template_id')->nullable()->after('level')->comment('Associated permission template');
            $table->foreign('template_id')->references('id')->on('permission_templates')
                ->onDelete('set null')->onUpdate('cascade');
            $table->boolean('auto_sync_template')->default(false)->after('template_id')
                ->comment('Auto-sync permissions from template');

            // Performance indexes
            $table->index('parent_id');
            $table->index('template_id');
            $table->index(['level', 'parent_id'], 'idx_user_groups_hierarchy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_groups', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['template_id']);
            $table->dropIndex('idx__user_groups_hierarchy');
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['template_id']);
            $table->dropColumn(['parent_id', 'level', 'template_id', 'auto_sync_template']);
        });
    }
};
