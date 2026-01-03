<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create permission_delegations table
 *
 * Purpose: Permission delegation system with validity periods
 * Features: Re-delegation support, revocation tracking, mandatory expiration
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
        Schema::create('permission_delegations', function (Blueprint $table) {
            $table->id();

            // Who is delegating?
            $table->unsignedBigInteger('delegator_id')->comment('User delegating the permission');
            $table->foreign('delegator_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('delegator_name', 255)->comment('Delegator name snapshot');

            // To whom?
            $table->unsignedBigInteger('delegatee_id')->comment('User receiving the permission');
            $table->foreign('delegatee_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('delegatee_name', 255)->comment('Delegatee name snapshot');

            // Which permission?
            $table->unsignedBigInteger('permission_id')->comment('Delegated permission');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->string('permission_slug', 255)->comment('Permission slug snapshot');

            // Scope?
            $table->unsignedBigInteger('scope_id')->nullable()->comment('Scoped delegation');
            $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');

            // Validity period (MANDATORY expiration)
            $table->timestamp('valid_from')->useCurrent()->comment('Delegation start date');
            $table->timestamp('valid_until')->nullable(false)->comment('Delegation end date (required)');

            // Re-delegation controls
            $table->boolean('can_redelegate')->default(false)->comment('Allow delegatee to redelegate');
            $table->integer('max_redelegation_depth')->default(0)->comment('Max redelegation chain depth');

            // Metadata
            $table->text('reason')->nullable()->comment('Delegation justification');
            $table->json('metadata')->nullable()->comment('Additional context');

            // Revocation tracking
            $table->timestamp('revoked_at')->nullable()->index()->comment('Revocation timestamp');
            $table->unsignedBigInteger('revoked_by')->nullable()->comment('User who revoked');
            $table->foreign('revoked_by')->references('id')->on('users')->onDelete('set null');
            $table->text('revocation_reason')->nullable()->comment('Why it was revoked');

            $table->timestamps();

            // Performance indexes
            $table->index('delegator_id');
            $table->index('delegatee_id');
            $table->index(['valid_from', 'valid_until'], 'idx_permission_delegations_validity');
            $table->index(['delegatee_id', 'revoked_at', 'valid_until'], 'idx_permission_delegations_active_delegations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_delegations');
    }
};
