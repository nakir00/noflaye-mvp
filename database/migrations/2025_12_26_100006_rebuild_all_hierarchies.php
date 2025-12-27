<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Rebuild all hierarchies
 *
 * Purpose: Recalculate closure tables and levels for:
 *          - permission_templates
 *          - user_groups
 *          - permission_groups
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
        echo "🔄 Starting hierarchy rebuild...\n";

        DB::transaction(function () {
            // STEP 1: Rebuild permission_template_hierarchy
            echo "  → Rebuilding permission template hierarchy...\n";
            $this->rebuildTemplateHierarchy();

            // STEP 2: Rebuild user_group_hierarchy
            echo "  → Rebuilding user group hierarchy...\n";
            $this->rebuildUserGroupHierarchy();

            // STEP 3: Rebuild permission_group_hierarchy
            echo "  → Rebuilding permission group hierarchy...\n";
            $this->rebuildPermissionGroupHierarchy();

            // STEP 4: Recalculate levels
            echo "  → Recalculating hierarchy levels...\n";
            $this->recalculateLevels();

            // Validation
            $templateHierarchyCount = DB::table('permission_template_hierarchy')->count();
            $userGroupHierarchyCount = DB::table('user_group_hierarchy')->count();
            $permGroupHierarchyCount = DB::table('permission_group_hierarchy')->count();

            echo "✅ Rebuilt template hierarchy: {$templateHierarchyCount} entries\n";
            echo "✅ Rebuilt user group hierarchy: {$userGroupHierarchyCount} entries\n";
            echo "✅ Rebuilt permission group hierarchy: {$permGroupHierarchyCount} entries\n";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Clearing all hierarchies...\n";

        DB::transaction(function () {
            DB::table('permission_template_hierarchy')->truncate();
            DB::table('user_group_hierarchy')->truncate();
            DB::table('permission_group_hierarchy')->truncate();

            // Reset levels
            DB::table('permission_templates')->update(['level' => 0]);
            DB::table('user_groups')->update(['level' => 0]);
            DB::table('permission_groups')->update(['level' => 0]);

            echo "✅ Cleared all hierarchies\n";
        });
    }

    /**
     * Rebuild permission template hierarchy
     */
    private function rebuildTemplateHierarchy(): void
    {
        // Clear existing
        DB::table('permission_template_hierarchy')->truncate();

        // Get all templates with parent
        $templates = DB::table('permission_templates')
            ->select('id', 'parent_id')
            ->whereNotNull('parent_id')
            ->get();

        $inserts = [];

        foreach ($templates as $template) {
            $ancestors = $this->findAncestors('permission_templates', $template->id);

            foreach ($ancestors as $depth => $ancestorId) {
                $inserts[] = [
                    'ancestor_id' => $ancestorId,
                    'descendant_id' => $template->id,
                    'depth' => $depth,
                ];
            }
        }

        if (!empty($inserts)) {
            DB::table('permission_template_hierarchy')->insert($inserts);
        }
    }

    /**
     * Rebuild user group hierarchy
     */
    private function rebuildUserGroupHierarchy(): void
    {
        DB::table('user_group_hierarchy')->truncate();

        $groups = DB::table('user_groups')
            ->select('id', 'parent_id')
            ->whereNotNull('parent_id')
            ->get();

        $inserts = [];

        foreach ($groups as $group) {
            $ancestors = $this->findAncestors('user_groups', $group->id);

            foreach ($ancestors as $depth => $ancestorId) {
                $inserts[] = [
                    'ancestor_id' => $ancestorId,
                    'descendant_id' => $group->id,
                    'depth' => $depth,
                ];
            }
        }

        if (!empty($inserts)) {
            DB::table('user_group_hierarchy')->insert($inserts);
        }
    }

    /**
     * Rebuild permission group hierarchy
     */
    private function rebuildPermissionGroupHierarchy(): void
    {
        DB::table('permission_group_hierarchy')->truncate();

        $groups = DB::table('permission_groups')
            ->select('id', 'parent_id')
            ->whereNotNull('parent_id')
            ->get();

        $inserts = [];

        foreach ($groups as $group) {
            $ancestors = $this->findAncestors('permission_groups', $group->id);

            foreach ($ancestors as $depth => $ancestorId) {
                $inserts[] = [
                    'ancestor_id' => $ancestorId,
                    'descendant_id' => $group->id,
                    'depth' => $depth,
                ];
            }
        }

        if (!empty($inserts)) {
            DB::table('permission_group_hierarchy')->insert($inserts);
        }
    }

    /**
     * Find all ancestors recursively
     */
    private function findAncestors(string $table, int $id, int $depth = 0): array
    {
        $ancestors = [];

        $parent = DB::table($table)
            ->where('id', $id)
            ->value('parent_id');

        if ($parent) {
            $ancestors[$depth] = $parent;
            $ancestors = array_merge($ancestors, $this->findAncestors($table, $parent, $depth + 1));
        }

        return $ancestors;
    }

    /**
     * Recalculate levels for all hierarchical tables
     */
    private function recalculateLevels(): void
    {
        // Permission templates
        $templates = DB::table('permission_templates')->get();
        foreach ($templates as $template) {
            $level = DB::table('permission_template_hierarchy')
                ->where('descendant_id', $template->id)
                ->max('depth') ?? 0;

            DB::table('permission_templates')
                ->where('id', $template->id)
                ->update(['level' => $level]);
        }

        // User groups
        $groups = DB::table('user_groups')->get();
        foreach ($groups as $group) {
            $level = DB::table('user_group_hierarchy')
                ->where('descendant_id', $group->id)
                ->max('depth') ?? 0;

            DB::table('user_groups')
                ->where('id', $group->id)
                ->update(['level' => $level]);
        }

        // Permission groups
        $permGroups = DB::table('permission_groups')->get();
        foreach ($permGroups as $group) {
            $level = DB::table('permission_group_hierarchy')
                ->where('descendant_id', $group->id)
                ->max('depth') ?? 0;

            DB::table('permission_groups')
                ->where('id', $group->id)
                ->update(['level' => $level]);
        }
    }
};
