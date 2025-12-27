<?php

namespace App\Observers;

use App\Models\UserGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UserGroupObserver
 *
 * Handle UserGroup lifecycle events
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class UserGroupObserver
{
    /**
     * Handle the UserGroup "created" event
     */
    public function created(UserGroup $userGroup): void
    {
        // Rebuild hierarchy if has parent
        if ($userGroup->parent_id) {
            $this->rebuildHierarchyForGroup($userGroup);
        }

        // Auto-assign template if configured
        if ($userGroup->template_id && $userGroup->auto_sync_template) {
            $this->syncTemplateToUsers($userGroup);
        }

        Log::info('UserGroup created', [
            'group_id' => $userGroup->id,
            'name' => $userGroup->name,
            'parent_id' => $userGroup->parent_id,
        ]);
    }

    /**
     * Handle the UserGroup "updated" event
     */
    public function updated(UserGroup $userGroup): void
    {
        // Rebuild hierarchy if parent changed
        if ($userGroup->isDirty('parent_id')) {
            $this->rebuildHierarchyForGroup($userGroup);

            Log::info('UserGroup hierarchy changed', [
                'group_id' => $userGroup->id,
                'old_parent_id' => $userGroup->getOriginal('parent_id'),
                'new_parent_id' => $userGroup->parent_id,
            ]);
        }

        // Re-sync template if changed or auto_sync enabled
        if ($userGroup->isDirty('template_id') && $userGroup->auto_sync_template) {
            $this->syncTemplateToUsers($userGroup);
        }
    }

    /**
     * Handle the UserGroup "deleting" event
     */
    public function deleting(UserGroup $userGroup): void
    {
        // Soft delete children groups
        $userGroup->children()->delete();

        Log::info('UserGroup deleting with children', [
            'group_id' => $userGroup->id,
            'children_count' => $userGroup->children()->count(),
        ]);
    }

    /**
     * Rebuild hierarchy for group
     */
    private function rebuildHierarchyForGroup(UserGroup $userGroup): void
    {
        // Clear existing hierarchy
        DB::table('user_group_hierarchy')
            ->where('descendant_id', $userGroup->id)
            ->delete();

        // Rebuild ancestors
        $ancestors = $this->findAncestors($userGroup->id);

        foreach ($ancestors as $depth => $ancestorId) {
            DB::table('user_group_hierarchy')->insert([
                'ancestor_id' => $ancestorId,
                'descendant_id' => $userGroup->id,
                'depth' => $depth,
            ]);
        }

        // Recalculate level
        $level = count($ancestors);
        $userGroup->update(['level' => $level]);
    }

    /**
     * Find all ancestors recursively
     */
    private function findAncestors(int $groupId, int $depth = 0): array
    {
        $ancestors = [];

        $parent = DB::table('user_groups')
            ->where('id', $groupId)
            ->value('parent_id');

        if ($parent) {
            $ancestors[$depth] = $parent;
            $ancestors = array_merge($ancestors, $this->findAncestors($parent, $depth + 1));
        }

        return $ancestors;
    }

    /**
     * Sync template permissions to all group users
     */
    private function syncTemplateToUsers(UserGroup $userGroup): void
    {
        if (!$userGroup->template_id) {
            return;
        }

        // This will be handled by a job in production
        // For now, just log
        Log::info('Template sync needed', [
            'group_id' => $userGroup->id,
            'template_id' => $userGroup->template_id,
            'users_count' => $userGroup->users()->count(),
        ]);
    }
}
