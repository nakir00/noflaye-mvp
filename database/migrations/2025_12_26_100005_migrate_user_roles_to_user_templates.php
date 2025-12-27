<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Migrate user_roles to user_templates
 *
 * Purpose: Copy user-role assignments to user-template assignments with scopes
 *          Also migrate users.primary_role_id to users.primary_template_id
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
        echo "🔄 Starting user roles to user templates migration...\n";

        DB::transaction(function () {
            // STEP 1: Retrieve user_roles
            echo "  → Fetching user_roles...\n";
            $userRoles = DB::table('user_roles')->get();
            $userRoleCount = $userRoles->count();

            if ($userRoleCount === 0) {
                echo "⚠️  No user_roles found to migrate\n";
            } else {
                echo "  → Preparing {$userRoleCount} user-template assignments...\n";

                // STEP 2: Build scope lookup map for performance
                echo "  → Building scope lookup map...\n";
                $scopeMap = DB::table('scopes')
                    ->get()
                    ->mapWithKeys(function ($scope) {
                        $key = $scope->scopable_type . ':' . $scope->scopable_id;
                        return [$key => $scope->id];
                    });

                // STEP 3: Prepare inserts
                $inserts = [];

                foreach ($userRoles as $ur) {
                    // Find scope_id from scopes table
                    $scopeId = null;

                    if ($ur->scope_type && $ur->scope_id) {
                        $key = $ur->scope_type . ':' . $ur->scope_id;
                        $scopeId = $scopeMap[$key] ?? null;
                    }

                    $inserts[] = [
                        'user_id' => $ur->user_id,
                        'template_id' => $ur->role_id, // role_id = template_id
                        'scope_id' => $scopeId,
                        'template_version' => null,
                        'auto_upgrade' => true,
                        'auto_sync' => true,
                        'valid_from' => $ur->valid_from ?? null,
                        'valid_until' => $ur->valid_until ?? null,
                        'reason' => $ur->reason ?? null,
                        'granted_by' => $ur->granted_by ?? null,
                        'created_at' => $ur->created_at ?? now(),
                        'updated_at' => $ur->updated_at ?? now(),
                    ];
                }

                // STEP 4: Bulk insert (chunked)
                echo "  → Inserting user templates (chunked)...\n";
                foreach (array_chunk($inserts, 1000) as $chunk) {
                    DB::table('user_templates')->insert($chunk);
                }

                echo "  ✓ Migrated {$userRoleCount} user-template assignments\n";
            }

            // STEP 5: Migrate users.primary_role_id → primary_template_id
            echo "  → Migrating users.primary_role_id to primary_template_id...\n";
            $updatedUsers = DB::table('users')
                ->whereNotNull('primary_role_id')
                ->update([
                    'primary_template_id' => DB::raw('primary_role_id'),
                ]);

            // Validation
            $expected = $userRoleCount;
            $actual = DB::table('user_templates')->count();

            echo "✅ Migrated {$actual} user-template assignments\n";
            echo "✅ Updated {$updatedUsers} users with primary_template_id\n";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Removing migrated user templates...\n";

        DB::transaction(function () {
            DB::table('user_templates')->truncate();

            DB::table('users')
                ->whereNotNull('primary_template_id')
                ->update(['primary_template_id' => null]);

            echo "✅ Removed all user templates and reset primary_template_id\n";
        });
    }
};
