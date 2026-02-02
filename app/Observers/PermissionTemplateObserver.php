<?php

namespace App\Observers;

use App\Models\PermissionTemplate;
use App\Services\Permissions\PermissionChecker;
use App\Services\Permissions\WildcardExpander;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PermissionTemplateObserver
 *
 * Handle PermissionTemplate lifecycle events
 *
 * Note: Hierarchy management (parent/children relationships) is handled by
 * the HasHierarchy trait via model events. This observer handles only
 * business logic like wildcard expansion, user sync, and caching.
 *
 * @author Noflaye Box Team
 *
 * @version 2.0.0
 */
class PermissionTemplateObserver
{
    public function __construct(
        private WildcardExpander $wildcardExpander,
        private PermissionChecker $permissionChecker
    ) {}

    /**
     * Handle the PermissionTemplate "creating" event (before save)
     */
    public function creating(PermissionTemplate $template): void
    {
        // Auto-generate slug if not provided
        if (empty($template->slug) && ! empty($template->name)) {
            $template->slug = Str::slug($template->name);
        }
    }

    /**
     * Handle the PermissionTemplate "saved" event
     */
    public function saved(PermissionTemplate $template): void
    {
        // Expand wildcards if changed
        if ($template->wasRecentlyCreated || $template->wasChanged()) {
            foreach ($template->wildcards as $wildcard) {
                $this->wildcardExpander->rebuildExpansions($wildcard);
            }
        }

        // Sync users if auto_sync enabled and permissions changed
        if ($template->auto_sync_users && $template->wasChanged()) {
            $this->syncUsersWithTemplate($template);
        }

        // Note: Hierarchy rebuilding is handled by HasHierarchy trait

        // Clear template cache
        $this->clearTemplateCache();
    }

    /**
     * Handle the PermissionTemplate "deleting" event
     */
    public function deleting(PermissionTemplate $template): bool
    {
        // Prevent deletion if users assigned
        $usersCount = $template->users()->count();

        if ($usersCount > 0) {
            Log::warning('Attempted to delete template with users', [
                'permission_template_id' => $template->id,
                'users_count' => $usersCount,
            ]);

            throw new \Exception("Cannot delete template with {$usersCount} users assigned");
        }

        // Note: Children soft-deletion and hierarchy cleanup is handled by HasHierarchy trait

        return true;
    }

    /**
     * Sync template to all assigned users
     */
    private function syncUsersWithTemplate(PermissionTemplate $template): void
    {
        $users = $template->users()
            ->wherePivot('auto_sync', true)
            ->get();

        foreach ($users as $user) {
            // Invalidate user permission cache
            $this->permissionChecker->invalidateUserCache($user);
        }

        Log::info('Template synced to users', [
            'permission_template_id' => $template->id,
            'users_count' => $users->count(),
        ]);
    }

    /**
     * Clear template-related cache
     */
    private function clearTemplateCache(): void
    {
        Cache::tags(['templates', 'permissions'])->flush();
    }
}
