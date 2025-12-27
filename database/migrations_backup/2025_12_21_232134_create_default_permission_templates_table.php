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
        Schema::create('default_permission_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('scope_type')->nullable(); // 'global', 'shop', 'kitchen', etc.
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['scope_type', 'scope_id']);
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_permission_templates');
    }
};
