<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add permission_type for override system
 *
 * Purpose: Allow templates and groups to grant or deny permissions
 * Features: Enables children to override inherited permissions
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
        // Add permission_type to template_permissions
        Schema::table('template_permissions', function (Blueprint $table) {
            $table->enum('permission_type', ['grant', 'deny'])
                ->default('grant')
                ->after('source')
                ->comment('grant=allow permission, deny=explicitly block (override parent)');
        });

        // Add permission_type to user_group_permissions if it doesn't exist
        if (! Schema::hasColumn('user_group_permissions', 'permission_type')) {
            Schema::table('user_group_permissions', function (Blueprint $table) {
                $table->enum('permission_type', ['grant', 'deny'])
                    ->default('grant')
                    ->after('permission_id')
                    ->comment('grant=allow permission, deny=explicitly block (override parent)');
            });
        }

        // Add source tracking to user_group_permissions for consistency
        if (! Schema::hasColumn('user_group_permissions', 'source')) {
            Schema::table('user_group_permissions', function (Blueprint $table) {
                $table->enum('source', ['direct', 'template', 'inherited'])
                    ->default('direct')
                    ->after('permission_type')
                    ->comment('direct=manually added, template=from template, inherited=from parent group');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_permissions', function (Blueprint $table) {
            $table->dropColumn('permission_type');
        });

        Schema::table('user_group_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('user_group_permissions', 'permission_type')) {
                $table->dropColumn('permission_type');
            }
            if (Schema::hasColumn('user_group_permissions', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
