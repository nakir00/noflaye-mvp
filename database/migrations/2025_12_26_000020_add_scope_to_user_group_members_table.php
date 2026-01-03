<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add scope to user_group_members table
 *
 * Purpose: Add scope support to group memberships
 * Features: Scoped group membership
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
        Schema::table('user_group_members', function (Blueprint $table) {
            // Scoped membership
            $table->unsignedBigInteger('scope_id')->nullable()->after('user_group_id')
                ->comment('Scope for group membership');
            $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');

            // Performance index
            $table->index('scope_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_group_members', function (Blueprint $table) {
            $table->dropForeign(['scope_id']);
            $table->dropIndex(['scope_id']);
            $table->dropColumn('scope_id');
        });
    }
};
