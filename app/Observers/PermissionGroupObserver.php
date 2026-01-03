<?php

namespace App\Observers;

use App\Models\PermissionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PermissionGroupObserver
 *
 * Handle PermissionGroup lifecycle events
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionGroupObserver
{
    /**
     * Handle the PermissionGroup "created" event
     */
    public function created(PermissionGroup $permissionGroup): void
    {
        // Rebuild hierarchy if has parent
        if ($permissionGroup->parent_id) {
            $this->rebuildHierarchyForGroup($permissionGroup);
        }

        Log::info('PermissionGroup created', [
            'group_id' => $permissionGroup->id,
            'name' => $permissionGroup->name,
            'parent_id' => $permissionGroup->parent_id,
        ]);
    }

    /**
     * Handle the PermissionGroup "updated" event
     */
    public function updated(PermissionGroup $permissionGroup): void
    {
        // Rebuild hierarchy if parent changed
        if ($permissionGroup->isDirty('parent_id')) {
            $this->rebuildHierarchyForGroup($permissionGroup);

            Log::info('PermissionGroup hierarchy changed', [
                'group_id' => $permissionGroup->id,
                'old_parent_id' => $permissionGroup->getOriginal('parent_id'),
                'new_parent_id' => $permissionGroup->parent_id,
            ]);
        }
    }

    /**
     * Handle the PermissionGroup "deleting" event
     */
    public function deleting(PermissionGroup $permissionGroup): void
    {
        // Soft delete children groups
        $permissionGroup->children()->delete();

        Log::info('PermissionGroup deleting with children', [
            'group_id' => $permissionGroup->id,
            'children_count' => $permissionGroup->children()->count(),
        ]);
    }

    /**
     * Rebuild hierarchy for group
     */
    private function rebuildHierarchyForGroup(PermissionGroup $permissionGroup): void
    {
        // Clear existing hierarchy
        DB::table('permission_group_hierarchy')
            ->where('descendant_id', $permissionGroup->id)
            ->delete();

        // Rebuild ancestors
        $ancestors = $this->findAncestors($permissionGroup->id);

        foreach ($ancestors as $depth => $ancestorId) {
            DB::table('permission_group_hierarchy')->insert([
                'ancestor_id' => $ancestorId,
                'descendant_id' => $permissionGroup->id,
                'depth' => $depth,
            ]);
        }

        // Recalculate level
        $level = count($ancestors);
        $permissionGroup->update(['level' => $level]);
    }

    /**
     * Find all ancestors recursively
     */
    private function findAncestors(int $groupId, int $depth = 0): array
    {
        $ancestors = [];

        $parent = DB::table('permission_groups')
            ->where('id', $groupId)
            ->value('parent_id');

        if ($parent) {
            $ancestors[$depth] = $parent;
            $ancestors = array_merge($ancestors, $this->findAncestors($parent, $depth + 1));
        }

        return $ancestors;
    }
}
