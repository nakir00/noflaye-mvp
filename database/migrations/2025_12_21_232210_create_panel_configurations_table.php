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
        Schema::create('panel_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('panel_id')->unique(); // 'shop', 'kitchen', etc.
            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_roles')->default(false);
            $table->boolean('can_manage_permissions')->default(false);
            $table->boolean('can_invite_users')->default(false);
            $table->boolean('can_assign_managers')->default(false);
            $table->boolean('can_create_templates')->default(false);
            $table->boolean('can_assign_templates')->default(false);
            $table->boolean('can_view_own_permissions')->default(true);
            $table->json('additional_settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_configurations');
    }
};
