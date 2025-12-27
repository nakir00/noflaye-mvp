<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create permission_rate_limits table
 *
 * Purpose: Track permission usage rate limits and abuse detection
 * Features: Per-user, per-permission, per-IP tracking
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
        Schema::create('permission_rate_limits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->comment('User who exceeded limit');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('permission', 255)->comment('Permission that was rate-limited');
            $table->string('ip_address', 45)->comment('IP address of the request');
            $table->text('user_agent')->nullable()->comment('Browser user agent');

            $table->timestamp('exceeded_at')->useCurrent()->comment('When limit was exceeded');

            // Performance indexes
            $table->index('user_id');
            $table->index('permission');
            $table->index('exceeded_at');
            $table->index(['user_id', 'permission', 'exceeded_at'], 'idx_user_permission_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_rate_limits');
    }
};
