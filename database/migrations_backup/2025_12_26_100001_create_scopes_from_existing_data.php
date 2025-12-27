<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Create scopes from existing data
 *
 * Purpose: Extract all unique scope_type/scope_id pairs from existing tables
 *          and create unified scope records
 *
 * Sources: user_permissions, user_roles, user_group_members
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
        echo "🔄 Starting scope creation from existing data...\n";

        DB::transaction(function () {
            // STEP 1: Collect all unique scope_type/scope_id pairs
            $scopesToCreate = collect();

            // Source 1: user_permissions
            echo "  → Extracting scopes from user_permissions...\n";
            $userPermScopes = DB::table('user_permissions')
                ->select('scope_type', 'scope_id')
                ->whereNotNull('scope_type')
                ->whereNotNull('scope_id')
                ->distinct()
                ->get();
            $scopesToCreate = $scopesToCreate->merge($userPermScopes);

            // Source 2: user_roles
            echo "  → Extracting scopes from user_roles...\n";
            $userRolesScopes = DB::table('user_roles')
                ->select('scope_type', 'scope_id')
                ->whereNotNull('scope_type')
                ->whereNotNull('scope_id')
                ->distinct()
                ->get();
            $scopesToCreate = $scopesToCreate->merge($userRolesScopes);

            // Source 3: user_group_members
            echo "  → Extracting scopes from user_group_members...\n";
            $groupMembersScopes = DB::table('user_group_members')
                ->select('scope_type', 'scope_id')
                ->whereNotNull('scope_type')
                ->whereNotNull('scope_id')
                ->distinct()
                ->get();
            $scopesToCreate = $scopesToCreate->merge($groupMembersScopes);

            // STEP 2: Deduplicate
            echo "  → Deduplicating scopes...\n";
            $uniqueScopes = $scopesToCreate->unique(function ($item) {
                return $item->scope_type . ':' . $item->scope_id;
            });

            // STEP 3: Prepare bulk insert with names
            echo "  → Preparing bulk insert data...\n";
            $inserts = [];
            foreach ($uniqueScopes as $scope) {
                $name = $this->getScopeName($scope->scope_type, $scope->scope_id);

                $inserts[] = [
                    'scopable_type' => $scope->scope_type,
                    'scopable_id' => $scope->scope_id,
                    'scope_key' => $this->makeScopeKey($scope->scope_type, $scope->scope_id),
                    'name' => $name,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // STEP 4: Bulk insert
            if (!empty($inserts)) {
                echo "  → Inserting " . count($inserts) . " scopes...\n";
                DB::table('scopes')->insert($inserts);
            }

            // STEP 5: Validation
            $expected = count($inserts);
            $actual = DB::table('scopes')->count();

            if ($expected !== $actual) {
                throw new \Exception("Scope creation mismatch: expected {$expected}, got {$actual}");
            }

            echo "✅ Created {$actual} scopes\n";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Removing all scopes...\n";
        DB::table('scopes')->truncate();
        echo "✅ Scopes removed\n";
    }

    /**
     * Get scope name from source table
     */
    private function getScopeName(string $type, int $id): ?string
    {
        $table = match($type) {
            'App\\Models\\Shop' => 'shops',
            'App\\Models\\Kitchen' => 'kitchens',
            'App\\Models\\Driver' => 'drivers',
            'App\\Models\\Supervisor' => 'supervisors',
            'App\\Models\\Supplier' => 'suppliers',
            default => null,
        };

        if (!$table) {
            return null;
        }

        return DB::table($table)->where('id', $id)->value('name');
    }

    /**
     * Make scope key from type and id
     */
    private function makeScopeKey(string $type, int $id): string
    {
        $shortType = strtolower(class_basename($type));
        return "{$shortType}:{$id}";
    }
};
