<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create delegation_chains table
 *
 * Purpose: Track delegation chains for re-delegated permissions
 * Features: Parent tracking, depth calculation for chain limits
 *
 * CORRECTION: Table name changed from 'delegation_chain' (singular) to 'delegation_chains' (plural)
 * Reason: Laravel convention uses plural table names, Model expects 'delegation_chains'
 *
 * @author Noflaye Box Team
 * @version 1.0.1
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delegation_chains', function (Blueprint $table) {
            $table->id();

            // Current delegation reference
            $table->unsignedBigInteger('delegation_id')->comment('Current delegation ID');
            $table->foreign('delegation_id')->references('id')->on('permission_delegations')->onDelete('cascade');

            // Parent delegation (for re-delegation chains)
            $table->unsignedBigInteger('parent_delegation_id')->nullable()->comment('Parent delegation if re-delegated');
            $table->foreign('parent_delegation_id')->references('id')->on('permission_delegations')->onDelete('cascade');

            // Chain depth tracking
            $table->integer('depth')->default(0)->comment('0=original delegation, 1=first re-delegation, etc.');

            // Chain path (JSON array of all delegation IDs in chain)
            $table->json('chain_path')->nullable()->comment('Array of delegation IDs from root to current');

            // Timestamps
            $table->timestamps();

            // Performance indexes
            $table->index('delegation_id');
            $table->index('parent_delegation_id');
            $table->index('depth');
            $table->index(['delegation_id', 'depth'], 'idx_delegation_depth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegation_chains');
    }
};
