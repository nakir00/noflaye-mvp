<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Migrate role_permissions to template_permissions
 *
 * Purpose: Copy role-permission associations to template-permission associations
 *          Including default template permissions from template_roles
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
        echo "🔄 Starting role permissions to template permissions migration...\n";

        DB::transaction(function () {
            // STEP 1: Migrate role_permissions
            echo "  → Fetching role_permissions...\n";
            $rolePermissions = DB::table('role_permissions')->get();
            $rolePermCount = $rolePermissions->count();

            if ($rolePermCount > 0) {
                echo "  → Preparing {$rolePermCount} template-permission associations...\n";
                $inserts = [];

                foreach ($rolePermissions as $rp) {
                    $inserts[] = [
                        'template_id' => $rp->role_id, // role_id = template_id (ID preserved)
                        'permission_id' => $rp->permission_id,
                        'source' => 'direct',
                        'wildcard_id' => null,
                        'sort_order' => 0,
                        'created_at' => $rp->created_at ?? now(),
                        'updated_at' => $rp->updated_at ?? now(),
                    ];
                }

                // Bulk insert with chunking
                echo "  → Inserting template permissions (chunked)...\n";
                foreach (array_chunk($inserts, 1000) as $chunk) {
                    DB::table('template_permissions')->insert($chunk);
                }

                echo "  ✓ Migrated {$rolePermCount} role-permission associations\n";
            } else {
                echo "⚠️  No role_permissions found\n";
            }

            // STEP 2: Migrate template_roles (default templates)
            echo "  → Fetching template_roles...\n";
            $idMapping = cache()->get('default_template_id_mapping', []);

            if (empty($idMapping)) {
                echo "⚠️  No ID mapping found, skipping template_roles migration\n";
                return;
            }

            $templateRoles = DB::table('template_roles')->get();
            $templateInserts = [];

            foreach ($templateRoles as $tr) {
                $newTemplateId = $idMapping[$tr->default_permission_template_id] ?? null;

                if (!$newTemplateId) {
                    echo "  ⚠️  Skipping template_role {$tr->id}: mapping not found\n";
                    continue;
                }

                // Fetch permissions for this role
                $rolePermissions = DB::table('role_permissions')
                    ->where('role_id', $tr->role_id)
                    ->get();

                foreach ($rolePermissions as $rp) {
                    $templateInserts[] = [
                        'template_id' => $newTemplateId,
                        'permission_id' => $rp->permission_id,
                        'source' => 'inherited',
                        'wildcard_id' => null,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($templateInserts)) {
                echo "  → Inserting " . count($templateInserts) . " inherited template permissions (chunked)...\n";
                foreach (array_chunk($templateInserts, 1000) as $chunk) {
                    DB::table('template_permissions')->insert($chunk);
                }
            }

            $total = $rolePermCount + count($templateInserts);
            echo "✅ Migrated {$total} template-permission associations\n";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Removing migrated template permissions...\n";

        DB::transaction(function () {
            DB::table('template_permissions')->truncate();
            echo "✅ Removed all template permissions\n";
        });
    }
};
