<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add sort_order to user_groups table
 *
 * Purpose: Allow ordering of user groups within the same parent level
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
        if (! Schema::hasColumn('user_groups', 'sort_order')) {
            Schema::table('user_groups', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('level')
                    ->comment('Display order among siblings');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_groups', function (Blueprint $table) {
            if (Schema::hasColumn('user_groups', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
