<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create permission_audit_log table
 *
 * Purpose: Comprehensive audit trail for permission changes
 * Features: Who, what, when, where, why tracking with full metadata
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
        Schema::create('permission_audit_log', function (Blueprint $table) {
            $table->id();

            // Who was affected?
            $table->unsignedBigInteger('user_id')->nullable()->comment('Affected user ID');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('user_name', 255)->nullable()->comment('User name snapshot');
            $table->string('user_email', 255)->nullable()->comment('User email snapshot');

            // What happened?
            $table->string('action', 50)->index()->comment('granted, revoked, updated, etc.');
            $table->string('permission_slug', 255)->index()->comment('Permission identifier');
            $table->string('permission_name', 255)->nullable()->comment('Permission name snapshot');

            // What was the source?
            $table->string('source', 50)->index()->comment('role, template, direct, delegation, etc.');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Source entity ID');
            $table->string('source_name', 255)->nullable()->comment('Source entity name');

            // What was the scope?
            $table->unsignedBigInteger('scope_id')->nullable()->comment('Scope reference');
            $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('set null');

            // Who performed the action?
            $table->unsignedBigInteger('performed_by')->nullable()->comment('Admin who made the change');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->string('performed_by_name', 255)->nullable()->comment('Admin name snapshot');

            // Why was it done?
            $table->text('reason')->nullable()->comment('Justification or reason');
            $table->json('metadata')->nullable()->comment('Additional context data');

            // Where and when?
            $table->string('ip_address', 45)->nullable()->comment('IP address (IPv4/IPv6)');
            $table->text('user_agent')->nullable()->comment('Browser user agent');
            $table->timestamp('created_at')->useCurrent()->index()->comment('Event timestamp');

            // Composite indexes for common queries
            $table->index(['user_id', 'created_at'], 'idx_user_date');
            $table->index(['permission_slug', 'created_at'], 'idx_permission_date');
            $table->index(['action', 'created_at'], 'idx_action_date');
            $table->index(['source', 'source_id'], 'idx_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_log');
    }
};
