<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add hierarchy columns to permission_groups table
 *
 * Purpose: Add parent_id and level to permission_groups
 * Features: Hierarchical permission grouping
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
        Schema::table('permission_groups', function (Blueprint $table) {
            // Hierarchical structure
            $table->unsignedBigInteger('parent_id')->nullable()->after('id')->comment('Parent permission group ID');
            $table->foreign('parent_id')->references('id')->on('permission_groups')
                ->onDelete('set null')->onUpdate('cascade');
            $table->integer('level')->default(0)->after('parent_id')->comment('Hierarchy level (0=root)');

            // Performance indexes
            $table->index('parent_id');
            $table->index(['level', 'parent_id'], 'idx_permission_groups_hierarchy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_groups', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex('idx_permission_groups_hierarchy');
            $table->dropIndex(['parent_id']);
            $table->dropColumn(['parent_id', 'level']);
        });
    }
};
