<?php

namespace App\Observers;

use App\Models\UserGroup;
use Illuminate\Support\Facades\Log;

/**
 * UserGroupObserver
 *
 * Handle UserGroup lifecycle events
 *
 * Note: Hierarchy management (parent/children relationships) is handled by
 * the HasHierarchy trait via model events. This observer handles only
 * business logic like template sync and logging.
 *
 * @author Noflaye Box Team
 *
 * @version 2.0.0
 */
class UserGroupObserver
{
    /**
     * Handle the UserGroup "created" event
     */
    public function created(UserGroup $userGroup): void
    {
        // Note: Hierarchy rebuilding is handled by HasHierarchy trait

        // Auto-assign template if configured
        if ($userGroup->permission_template_id && $userGroup->auto_sync_template) {
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
        // Note: Hierarchy rebuilding is handled by HasHierarchy trait

        // Log hierarchy changes
        if ($userGroup->wasChanged('parent_id')) {
            Log::info('UserGroup hierarchy changed', [
                'group_id' => $userGroup->id,
                'old_parent_id' => $userGroup->getOriginal('parent_id'),
                'new_parent_id' => $userGroup->parent_id,
            ]);
        }

        // Re-sync template if changed or auto_sync enabled
        if ($userGroup->wasChanged('permission_template_id') && $userGroup->auto_sync_template) {
            $this->syncTemplateToUsers($userGroup);
        }
    }

    /**
     * Handle the UserGroup "deleting" event
     */
    public function deleting(UserGroup $userGroup): void
    {
        // Note: Children soft-deletion and hierarchy cleanup is handled by HasHierarchy trait

        Log::info('UserGroup deleting', [
            'group_id' => $userGroup->id,
            'children_count' => $userGroup->children()->count(),
        ]);
    }

    /**
     * Sync template permissions to all group users
     */
    private function syncTemplateToUsers(UserGroup $userGroup): void
    {
        if (! $userGroup->permission_template_id) {
            return;
        }

        // This will be handled by a job in production
        // For now, just log
        Log::info('Template sync needed', [
            'group_id' => $userGroup->id,
            'permission_template_id' => $userGroup->permission_template_id,
            'users_count' => $userGroup->users()->count(),
        ]);
    }
}
