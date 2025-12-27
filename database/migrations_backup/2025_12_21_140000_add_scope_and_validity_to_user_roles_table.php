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
        Schema::table('user_roles', function (Blueprint $table) {
            // Drop the existing unique constraint first
            $table->dropUnique(['user_id', 'role_id']);

            // Scope (multi-tenancy)
            $table->string('scope_type', 50)->nullable()->after('role_id')
                ->comment('Type: shop, supplier, region, null=global');
            $table->unsignedBigInteger('scope_id')->nullable()->after('scope_type')
                ->comment('ID du scope (shop_id, supplier_id, etc.)');

            // Validité temporelle
            $table->timestamp('valid_from')->default(now())->after('scope_id');
            $table->timestamp('valid_until')->nullable()->after('valid_from')
                ->comment('NULL = permanent');

            // Métadonnées
            $table->unsignedBigInteger('granted_by')->nullable()->after('valid_until')
                ->comment('User qui a attribué ce rôle');
            $table->text('reason')->nullable()->after('granted_by');

            // Index
            $table->index(['scope_type', 'scope_id']);
            $table->index(['valid_from', 'valid_until']);

            // Foreign key
            $table->foreign('granted_by')->references('id')->on('users')->nullOnDelete();

            // New unique constraint
            $table->unique(['user_id', 'role_id', 'scope_type', 'scope_id'], 'user_role_scope_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('user_role_scope_unique');

            // Drop foreign key
            $table->dropForeign(['granted_by']);

            // Drop indexes
            $table->dropIndex(['scope_type', 'scope_id']);
            $table->dropIndex(['valid_from', 'valid_until']);

            // Drop columns
            $table->dropColumn([
                'scope_type',
                'scope_id',
                'valid_from',
                'valid_until',
                'granted_by',
                'reason',
            ]);

            // Restore original unique constraint
            $table->unique(['user_id', 'role_id']);
        });
    }
};
