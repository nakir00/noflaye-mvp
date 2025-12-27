<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create delegation_chain table
 *
 * Purpose: Track delegation chains for re-delegated permissions
 * Features: Parent tracking, depth calculation for chain limits
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delegation_chain', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('delegation_id')->comment('Current delegation ID');
            $table->foreign('delegation_id')->references('id')->on('permission_delegations')->onDelete('cascade');

            $table->unsignedBigInteger('parent_delegation_id')->nullable()->comment('Parent delegation if re-delegated');
            $table->foreign('parent_delegation_id')->references('id')->on('permission_delegations')->onDelete('cascade');

            $table->integer('depth')->default(0)->comment('0=original delegation, 1=first re-delegation');

            // Performance indexes
            $table->index('delegation_id');
            $table->index('parent_delegation_id');
            $table->index(['delegation_id', 'depth'], 'idx_delegation_depth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegation_chain');
    }
};
