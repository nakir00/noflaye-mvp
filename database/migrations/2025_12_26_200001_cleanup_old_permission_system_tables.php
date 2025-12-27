<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Cleanup old permission system tables
 *
 * Purpose: Remove obsolete tables and columns after successful migration
 *          to new unified permission system
 *
 * WARNING: This migration drops tables permanently!
 *          Ensure backup before running in production.
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
        echo "🧹 Starting cleanup of old permission system...\n\n";

        DB::transaction(function () {
            // Validation pre-cleanup
            $this->validateMigrationComplete();

            // Backup recommendations
            $this->showBackupRecommendations();

            // Drop pivot tables (respecting FK order)
            echo "🗑️  Dropping pivot tables...\n";
            Schema::dropIfExists('template_roles');
            Schema::dropIfExists('role_permissions');
            Schema::dropIfExists('role_hierarchy');
            Schema::dropIfExists('user_roles');
            echo "  ✓ Dropped 4 pivot tables\n\n";

            // Drop main tables
            echo "🗑️  Dropping main tables...\n";
            Schema::dropIfExists('roles');
            Schema::dropIfExists('default_permission_templates');
            echo "  ✓ Dropped 2 main tables\n\n";

            // Drop obsolete columns
            echo "🗑️  Dropping obsolete columns...\n";

            if (Schema::hasColumn('users', 'primary_role_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['primary_role_id']);
                    $table->dropColumn('primary_role_id');
                });
                echo "  ✓ Dropped users.primary_role_id\n";
            }

            if (Schema::hasColumn('user_permissions', 'scope_type')) {
                Schema::table('user_permissions', function (Blueprint $table) {
                    $table->dropColumn('scope_type');
                });
                echo "  ✓ Dropped user_permissions.scope_type\n";
            }

            if (Schema::hasColumn('user_group_members', 'scope_type')) {
                Schema::table('user_group_members', function (Blueprint $table) {
                    $table->dropColumn('scope_type');
                });
                echo "  ✓ Dropped user_group_members.scope_type\n";
            }

            echo "\n";

            // Validation post-cleanup
            $this->validateCleanupComplete();
        });

        echo "\n✅ Cleanup completed successfully!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Old permission system fully removed.\n";
        echo "New unified system is now active.\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "⚠️  ROLLING BACK: Recreating old permission system tables...\n\n";

        DB::transaction(function () {
            // Recreate roles
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('slug', 255)->unique();
                $table->text('description')->nullable();
                $table->string('color', 50)->nullable();
                $table->string('icon', 100)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });

            // Recreate role_permissions
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');
                $table->timestamps();
                $table->unique(['role_id', 'permission_id']);
            });

            // Recreate role_hierarchy
            Schema::create('role_hierarchy', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_role_id');
                $table->unsignedBigInteger('child_role_id');
                $table->timestamps();
                $table->unique(['parent_role_id', 'child_role_id']);
            });

            // Recreate user_roles
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->string('scope_type', 255)->nullable();
                $table->unsignedBigInteger('scope_id')->nullable();
                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('granted_by')->nullable();
                $table->timestamps();
            });

            // Recreate default_permission_templates
            Schema::create('default_permission_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('slug', 255)->unique();
                $table->text('description')->nullable();
                $table->string('color', 50)->nullable();
                $table->string('icon', 100)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });

            // Recreate template_roles
            Schema::create('template_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('default_permission_template_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });

            // Recreate columns
            if (!Schema::hasColumn('users', 'primary_role_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedBigInteger('primary_role_id')->nullable()->after('id');
                });
            }

            if (!Schema::hasColumn('user_permissions', 'scope_type')) {
                Schema::table('user_permissions', function (Blueprint $table) {
                    $table->string('scope_type', 255)->nullable()->after('permission_id');
                });
            }

            if (!Schema::hasColumn('user_group_members', 'scope_type')) {
                Schema::table('user_group_members', function (Blueprint $table) {
                    $table->string('scope_type', 255)->nullable()->after('user_group_id');
                });
            }

            echo "✅ Old tables recreated (empty)\n";
            echo "⚠️  Note: Data NOT restored. Restore from backup if needed.\n";
        });
    }

    /**
     * Validate that all previous migrations completed successfully
     */
    private function validateMigrationComplete(): void
    {
        echo "🔍 Validating migration completion...\n";

        // Validate scopes created
        $scopesCount = DB::table('scopes')->count();
        if ($scopesCount === 0) {
            throw new \Exception("No scopes found! Run migration 100001 first.");
        }
        echo "  ✓ Scopes: {$scopesCount} entries\n";

        // Validate templates created
        $templatesCount = DB::table('permission_templates')->count();
        $rolesCount = DB::table('roles')->count();

        if ($templatesCount < $rolesCount) {
            throw new \Exception("Templates count ({$templatesCount}) < roles count ({$rolesCount}). Migration incomplete!");
        }
        echo "  ✓ Templates: {$templatesCount} entries (roles: {$rolesCount})\n";

        // Validate template_permissions created
        $templatePermsCount = DB::table('template_permissions')->count();
        $rolePermsCount = DB::table('role_permissions')->count();

        if ($templatePermsCount < $rolePermsCount) {
            throw new \Exception("Template permissions count ({$templatePermsCount}) < role permissions count ({$rolePermsCount}). Migration incomplete!");
        }
        echo "  ✓ Template Permissions: {$templatePermsCount} entries (role perms: {$rolePermsCount})\n";

        // Validate user_templates created
        $userTemplatesCount = DB::table('user_templates')->count();
        $userRolesCount = DB::table('user_roles')->count();

        if ($userTemplatesCount < $userRolesCount) {
            throw new \Exception("User templates count ({$userTemplatesCount}) < user roles count ({$userRolesCount}). Migration incomplete!");
        }
        echo "  ✓ User Templates: {$userTemplatesCount} entries (user roles: {$userRolesCount})\n";

        // Validate primary_template_id migrated
        $usersWithPrimaryRole = DB::table('users')->whereNotNull('primary_role_id')->count();
        $usersWithPrimaryTemplate = DB::table('users')->whereNotNull('primary_template_id')->count();

        if ($usersWithPrimaryTemplate < $usersWithPrimaryRole) {
            throw new \Exception("Users with primary_template_id ({$usersWithPrimaryTemplate}) < users with primary_role_id ({$usersWithPrimaryRole}). Migration incomplete!");
        }
        echo "  ✓ Primary Template ID: {$usersWithPrimaryTemplate} users\n";

        echo "✅ All validations passed. Safe to cleanup.\n\n";
    }

    /**
     * Display backup recommendations
     */
    private function showBackupRecommendations(): void
    {
        echo "⚠️  BACKUP RECOMMENDATIONS\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Before proceeding, it is STRONGLY RECOMMENDED to backup:\n\n";

        echo "Tables to backup:\n";
        echo "  • roles\n";
        echo "  • role_permissions\n";
        echo "  • role_hierarchy\n";
        echo "  • user_roles\n";
        echo "  • default_permission_templates\n";
        echo "  • template_roles\n\n";

        echo "Backup command:\n";
        echo "  mysqldump -u [user] -p [database] roles role_permissions role_hierarchy user_roles default_permission_templates template_roles > backup_old_permissions_\$(date +%Y%m%d_%H%M%S).sql\n\n";

        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }

    /**
     * Validate cleanup completed successfully
     */
    private function validateCleanupComplete(): void
    {
        echo "🔍 Validating cleanup completion...\n";

        // Validate tables dropped
        $droppedTables = [
            'roles',
            'role_permissions',
            'role_hierarchy',
            'user_roles',
            'default_permission_templates',
            'template_roles',
        ];

        foreach ($droppedTables as $table) {
            if (Schema::hasTable($table)) {
                throw new \Exception("Table {$table} still exists!");
            }
        }
        echo "  ✓ All old tables dropped\n";

        // Validate columns dropped
        if (Schema::hasColumn('users', 'primary_role_id')) {
            throw new \Exception("Column users.primary_role_id still exists!");
        }
        echo "  ✓ users.primary_role_id dropped\n";

        if (Schema::hasColumn('user_permissions', 'scope_type')) {
            throw new \Exception("Column user_permissions.scope_type still exists!");
        }
        echo "  ✓ user_permissions.scope_type dropped\n";

        if (Schema::hasColumn('user_group_members', 'scope_type')) {
            throw new \Exception("Column user_group_members.scope_type still exists!");
        }
        echo "  ✓ user_group_members.scope_type dropped\n";

        // Validate new structures intact
        $newTables = [
            'scopes',
            'permission_templates',
            'permission_wildcards',
            'template_permissions',
            'user_templates',
        ];

        foreach ($newTables as $table) {
            if (!Schema::hasTable($table)) {
                throw new \Exception("New table {$table} is missing!");
            }
        }
        echo "  ✓ All new tables intact\n";

        echo "✅ Cleanup validation passed\n";
    }
};
