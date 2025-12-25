<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Template Roles
        Schema::create('template_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('default_permission_templates')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'role_id']);
        });

        // Template Permissions
        Schema::create('template_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('default_permission_templates')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'permission_id']);
        });

        // Template User Groups
        Schema::create('template_user_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('default_permission_templates')->cascadeOnDelete();
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'user_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_user_groups');
        Schema::dropIfExists('template_permissions');
        Schema::dropIfExists('template_roles');
    }
};
