<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create permission_template_versions table
 *
 * Purpose: Version control for permission templates
 * Features: Snapshots, changelog, publish workflow
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
        Schema::create('permission_template_versions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('template_id')->comment('Permission template ID');
            $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');

            $table->integer('version')->comment('Version number');

            // Template snapshot
            $table->string('name', 255)->comment('Template name at this version');
            $table->string('slug', 255)->comment('Template slug at this version');
            $table->text('description')->nullable()->comment('Description snapshot');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent ID snapshot');
            $table->unsignedBigInteger('scope_id')->nullable()->comment('Scope ID snapshot');
            $table->string('color', 50)->nullable()->comment('Color snapshot');
            $table->string('icon', 100)->nullable()->comment('Icon snapshot');
            $table->integer('level')->default(0)->comment('Level snapshot');

            // Permissions and wildcards snapshots
            $table->json('permissions_snapshot')->comment('Array of [{id, slug, name}, ...]');
            $table->json('wildcards_snapshot')->nullable()->comment('Array of [{id, pattern}, ...]');

            // Version metadata
            $table->string('version_name', 255)->nullable()->comment('Human-readable version name (e.g., v2.0 - Analytics)');
            $table->text('changelog')->nullable()->comment('Changes in this version');
            $table->boolean('is_stable')->default(false)->index()->comment('Stable release flag');
            $table->boolean('is_published')->default(false)->index()->comment('Published to users');

            // Audit trail
            $table->unsignedBigInteger('created_by')->nullable()->comment('User who created version');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent()->comment('Version creation date');

            $table->timestamp('published_at')->nullable()->comment('Publication date');
            $table->unsignedBigInteger('published_by')->nullable()->comment('User who published');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');

            // Unique constraint and indexes
            $table->unique(['template_id', 'version'], 'unique_template_version');
            $table->index(['is_published', 'is_stable'], 'idx_published_stable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_template_versions');
    }
};
