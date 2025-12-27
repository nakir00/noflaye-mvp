<?php

namespace App\Observers;

use App\Models\PermissionTemplate;
use App\Services\Permissions\WildcardExpander;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PermissionTemplateObserver
 *
 * Handle PermissionTemplate lifecycle events
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionTemplateObserver
{
    public function __construct(
        private WildcardExpander $wildcardExpander,
        private PermissionChecker $permissionChecker
    ) {}

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

        // Rebuild hierarchy if parent changed
        if ($template->isDirty('parent_id')) {
            $this->rebuildHierarchyForTemplate($template);
        }
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
                'template_id' => $template->id,
                'users_count' => $usersCount,
            ]);

            throw new \Exception("Cannot delete template with {$usersCount} users assigned");
        }

        // Soft delete children templates
        $template->children()->delete();

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
            'template_id' => $template->id,
            'users_count' => $users->count(),
        ]);
    }

    /**
     * Rebuild hierarchy for template
     */
    private function rebuildHierarchyForTemplate(PermissionTemplate $template): void
    {
        DB::table('permission_template_hierarchy')
            ->where('descendant_id', $template->id)
            ->delete();

        $ancestors = $this->findAncestors($template->id);

        foreach ($ancestors as $depth => $ancestorId) {
            DB::table('permission_template_hierarchy')->insert([
                'ancestor_id' => $ancestorId,
                'descendant_id' => $template->id,
                'depth' => $depth,
            ]);
        }

        $level = count($ancestors);
        $template->update(['level' => $level]);
    }

    /**
     * Find all ancestors recursively
     */
    private function findAncestors(int $templateId, int $depth = 0): array
    {
        $ancestors = [];

        $parent = DB::table('permission_templates')
            ->where('id', $templateId)
            ->value('parent_id');

        if ($parent) {
            $ancestors[$depth] = $parent;
            $ancestors = array_merge($ancestors, $this->findAncestors($parent, $depth + 1));
        }

        return $ancestors;
    }
}
