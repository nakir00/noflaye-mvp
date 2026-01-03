<?php

namespace App\Observers;

use App\Models\Permission;
use App\Services\Permissions\WildcardExpander;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PermissionObserver
 *
 * Handle Permission lifecycle events
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionObserver
{
    public function __construct(
        private WildcardExpander $wildcardExpander
    ) {}

    /**
     * Handle the Permission "creating" event (before save)
     */
    public function creating(Permission $permission): void
    {
        // Auto-generate slug if not provided
        if (empty($permission->slug) && ! empty($permission->name)) {
            $permission->slug = Str::slug($permission->name);
        }
    }

    /**
     * Handle the Permission "created" event
     */
    public function created(Permission $permission): void
    {
        // Rebuild wildcard expansions
        $this->rebuildWildcardsForPermission($permission);

        // Clear permission cache
        $this->clearPermissionCache();

        Log::info('Permission created', [
            'permission_id' => $permission->id,
            'slug' => $permission->slug,
            'name' => $permission->name,
        ]);
    }

    /**
     * Handle the Permission "updated" event
     */
    public function updated(Permission $permission): void
    {
        // Rebuild wildcards if slug changed
        if ($permission->isDirty('slug')) {
            $this->rebuildWildcardsForPermission($permission);

            Log::info('Permission slug changed', [
                'permission_id' => $permission->id,
                'old_slug' => $permission->getOriginal('slug'),
                'new_slug' => $permission->slug,
            ]);
        }

        // Clear permission cache
        $this->clearPermissionCache();
    }

    /**
     * Handle the Permission "deleting" event
     */
    public function deleting(Permission $permission): void
    {
        // Check if permission is in use
        $usersCount = $permission->users()->count();
        $templatesCount = $permission->templates()->count();

        if ($usersCount > 0 || $templatesCount > 0) {
            Log::warning('Attempted to delete permission in use', [
                'permission_id' => $permission->id,
                'users_count' => $usersCount,
                'templates_count' => $templatesCount,
            ]);

            throw new \Exception("Cannot delete permission in use by {$usersCount} users and {$templatesCount} templates");
        }

        Log::info('Permission deleting', [
            'permission_id' => $permission->id,
            'slug' => $permission->slug,
        ]);
    }

    /**
     * Rebuild wildcard expansions that match this permission
     */
    private function rebuildWildcardsForPermission(Permission $permission): void
    {
        // Get all active wildcards
        $wildcards = \App\Models\PermissionWildcard::active()->autoExpand()->get();

        foreach ($wildcards as $wildcard) {
            // Check if this permission matches the wildcard pattern
            if ($this->wildcardExpander->matchesPattern($permission, $wildcard->pattern)) {
                $this->wildcardExpander->rebuildExpansions($wildcard);
            }
        }
    }

    /**
     * Clear permission-related cache
     */
    private function clearPermissionCache(): void
    {
        Cache::tags(['permissions'])->flush();
    }
}
