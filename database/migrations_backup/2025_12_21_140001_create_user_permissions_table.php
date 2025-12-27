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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();

            // Type de permission (grant/revoke)
            $table->enum('permission_type', ['grant', 'revoke'])->default('grant')
                ->comment('grant=accorder, revoke=retirer');

            // Scope (multi-tenancy)
            $table->string('scope_type', 50)->nullable()
                ->comment('Type: shop, supplier, region, null=global');
            $table->unsignedBigInteger('scope_id')->nullable()
                ->comment('ID du scope (shop_id, supplier_id, etc.)');

            // Validité temporelle
            $table->timestamp('valid_from')->default(now());
            $table->timestamp('valid_until')->nullable()
                ->comment('NULL = permanent');

            // Métadonnées
            $table->unsignedBigInteger('granted_by')->nullable()
                ->comment('User qui a attribué cette permission');
            $table->text('reason')->nullable()
                ->comment('Raison de l\'attribution/révocation');

            $table->timestamps();

            // Index
            $table->index(['user_id', 'permission_id']);
            $table->index(['scope_type', 'scope_id']);
            $table->index(['valid_from', 'valid_until']);
            $table->index('permission_type');

            // Foreign key
            $table->foreign('granted_by')->references('id')->on('users')->nullOnDelete();

            // Unique constraint
            $table->unique(['user_id', 'permission_id', 'permission_type', 'scope_type', 'scope_id'], 'user_permission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
