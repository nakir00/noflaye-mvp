<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create user_group_hierarchy table
 *
 * Purpose: Closure table for user group hierarchies
 * Features: Fast ancestor/descendant queries with depth tracking
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
        Schema::create('user_group_hierarchy', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('ancestor_id')->comment('Ancestor group ID');
            $table->foreign('ancestor_id')->references('id')->on('user_groups')->onDelete('cascade');

            $table->unsignedBigInteger('descendant_id')->comment('Descendant group ID');
            $table->foreign('descendant_id')->references('id')->on('user_groups')->onDelete('cascade');

            $table->integer('depth')->default(0)->comment('0=direct parent, 1=grandparent, etc.');

            // Unique constraint and indexes
            $table->unique(['ancestor_id', 'descendant_id'], 'unique_group_hierarchy');
            $table->index(['ancestor_id', 'depth'], 'idx_ancestor_depth');
            $table->index(['descendant_id', 'depth'], 'idx_descendant_depth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_group_hierarchy');
    }
};
