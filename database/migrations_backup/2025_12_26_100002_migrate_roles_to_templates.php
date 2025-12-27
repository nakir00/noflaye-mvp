<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Migration: Migrate roles to permission_templates
 *
 * Purpose: Copy all roles from roles table to permission_templates table
 *          Preserving IDs to avoid foreign key issues
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
        echo "🔄 Starting roles to templates migration...\n";

        DB::transaction(function () {
            // STEP 1: Retrieve all roles
            echo "  → Fetching all roles...\n";
            $roles = DB::table('roles')->get();
            $roleCount = $roles->count();

            if ($roleCount === 0) {
                echo "⚠️  No roles found to migrate\n";
                return;
            }

            // STEP 2: Prepare bulk insert
            echo "  → Preparing {$roleCount} templates for insert...\n";
            $inserts = [];

            foreach ($roles as $role) {
                $inserts[] = [
                    'id' => $role->id, // Preserve ID to avoid FK breaks
                    'name' => $role->name,
                    'slug' => $role->slug ?? Str::slug($role->name),
                    'description' => $role->description ?? null,
                    'parent_id' => null, // Will be handled by migration 6
                    'scope_id' => null,
                    'color' => $role->color ?? 'primary',
                    'icon' => $role->icon ?? 'heroicon-o-shield-check',
                    'level' => 0, // Will be recalculated by migration 6
                    'sort_order' => $role->sort_order ?? 0,
                    'is_active' => $role->is_active ?? true,
                    'is_system' => $role->is_system ?? false,
                    'auto_sync_users' => true,
                    'created_at' => $role->created_at ?? now(),
                    'updated_at' => $role->updated_at ?? now(),
                    'deleted_at' => $role->deleted_at ?? null,
                ];
            }

            // STEP 3: Bulk insert
            echo "  → Inserting templates...\n";
            DB::table('permission_templates')->insert($inserts);

            // STEP 4: Validation
            $expected = $roleCount;
            $actual = DB::table('permission_templates')
                ->whereIn('id', DB::table('roles')->pluck('id'))
                ->count();

            if ($expected !== $actual) {
                throw new \Exception("Role migration mismatch: expected {$expected}, got {$actual}");
            }

            echo "✅ Migrated {$expected} roles to templates\n";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Removing migrated templates...\n";

        DB::transaction(function () {
            $roleIds = DB::table('roles')->pluck('id');

            DB::table('permission_templates')
                ->whereIn('id', $roleIds)
                ->delete();

            echo "✅ Removed migrated templates\n";
        });
    }
};
