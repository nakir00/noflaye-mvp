<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add primary_template_id to users table
 *
 * Purpose: Add primary permission template reference for users
 * Features: Default template assignment
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
        Schema::table('users', function (Blueprint $table) {
            // Primary permission template
            $table->unsignedBigInteger('primary_template_id')->nullable()->after('id')
                ->comment('Primary permission template for user');
            $table->foreign('primary_template_id')->references('id')->on('permission_templates')
                ->onDelete('set null');

            // Performance index
            $table->index('primary_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['primary_template_id']);
            $table->dropIndex(['primary_template_id']);
            $table->dropColumn('primary_template_id');
        });
    }
};
