<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create permission_requests table
 *
 * Purpose: Permission request/approval workflow
 * Features: Status tracking, review process, audit trail
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
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->comment('User requesting permission');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('permission_id')->comment('Requested permission');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->unsignedBigInteger('scope_id')->nullable()->comment('Requested scope');
            $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');

            $table->text('reason')->comment('Justification for request');

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->index()
                ->comment('Request status');

            // Request workflow
            $table->timestamp('requested_at')->useCurrent()->comment('Request submission date');
            $table->timestamp('reviewed_at')->nullable()->comment('Review completion date');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('Reviewer user ID');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->text('review_comment')->nullable()->comment('Reviewer notes');

            $table->json('metadata')->nullable()->comment('Additional request context');
            $table->timestamps();

            // Performance indexes
            $table->index(['user_id', 'status'], 'idx_user_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};
