<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add sort_order to hierarchy tables
 *
 * Purpose: Allow ordering of children in hierarchical structures
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
        // Add sort_order to permission_template_hierarchy
        Schema::table('permission_template_hierarchy', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('depth')
                ->comment('Order among siblings at the same level');
        });

        // Add sort_order to user_group_hierarchy
        Schema::table('user_group_hierarchy', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('depth')
                ->comment('Order among siblings at the same level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_template_hierarchy', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('user_group_hierarchy', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
