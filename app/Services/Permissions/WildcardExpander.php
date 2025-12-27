<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use App\Models\PermissionWildcard;
use App\Models\PermissionTemplate;
use App\Enums\WildcardPattern;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WildcardExpander Service
 *
 * Expands wildcard patterns into concrete permissions
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class WildcardExpander
{
    /**
     * Expand a wildcard pattern into permissions
     *
     * @param string $pattern Wildcard pattern (e.g., "shops.*", "*.read")
     * @return Collection<Permission>
     */
    public function expand(string $pattern): Collection
    {
        // Full wildcard (*.*)
        if ($pattern === '*.*') {
            return Permission::all();
        }

        // Resource wildcard (shops.*)
        if (str_ends_with($pattern, '.*')) {
            $resource = str_replace('.*', '', $pattern);
            return Permission::where('slug', 'like', "{$resource}.%")->get();
        }

        // Action wildcard (*.read)
        if (str_starts_with($pattern, '*.')) {
            $action = str_replace('*.', '', $pattern);
            return Permission::where('slug', 'like', "%.{$action}")->get();
        }

        // Macro wildcard (specific pattern)
        return $this->expandMacro($pattern);
    }

    /**
     * Expand wildcard for a template
     *
     * @param PermissionTemplate $template
     * @return Collection<Permission>
     */
    public function expandForTemplate(PermissionTemplate $template): Collection
    {
        $permissions = collect();

        // Get all wildcards for this template
        $wildcards = $template->wildcards;

        foreach ($wildcards as $wildcard) {
            $expanded = $this->expand($wildcard->pattern);
            $permissions = $permissions->merge($expanded);
        }

        return $permissions->unique('id');
    }

    /**
     * Rebuild wildcard expansions (cache)
     *
     * @param PermissionWildcard $wildcard
     * @return int Number of permissions expanded
     */
    public function rebuildExpansions(PermissionWildcard $wildcard): int
    {
        DB::transaction(function () use ($wildcard) {
            // Clear existing expansions
            $wildcard->permissions()->detach();

            // Expand pattern
            $permissions = $this->expand($wildcard->pattern);

            // Attach with metadata
            $attachData = [];
            foreach ($permissions as $permission) {
                $attachData[$permission->id] = [
                    'is_auto_generated' => true,
                    'expanded_at' => now(),
                ];
            }

            $wildcard->permissions()->attach($attachData);

            // Update count
            $wildcard->markAsExpanded($permissions->count());

            Log::info("Wildcard expanded", [
                'wildcard_id' => $wildcard->id,
                'pattern' => $wildcard->pattern,
                'permissions_count' => $permissions->count(),
            ]);
        });

        return $wildcard->permissions_count;
    }

    /**
     * Check if permission matches pattern
     *
     * @param Permission $permission
     * @param string $pattern
     * @return bool
     */
    public function matchesPattern(Permission $permission, string $pattern): bool
    {
        // Full wildcard
        if ($pattern === '*.*') {
            return true;
        }

        // Resource wildcard (shops.*)
        if (str_ends_with($pattern, '.*')) {
            $resource = str_replace('.*', '', $pattern);
            return str_starts_with($permission->slug, "{$resource}.");
        }

        // Action wildcard (*.read)
        if (str_starts_with($pattern, '*.')) {
            $action = str_replace('*.', '', $pattern);
            return str_ends_with($permission->slug, ".{$action}");
        }

        // Macro pattern
        return $this->matchesMacro($permission, $pattern);
    }

    /**
     * Get all permissions matching multiple patterns
     *
     * @param array $patterns
     * @return Collection<Permission>
     */
    public function expandMultiple(array $patterns): Collection
    {
        $permissions = collect();

        foreach ($patterns as $pattern) {
            $expanded = $this->expand($pattern);
            $permissions = $permissions->merge($expanded);
        }

        return $permissions->unique('id');
    }

    /**
     * Auto-expand all active wildcards
     *
     * @return int Total permissions expanded
     */
    public function autoExpandAll(): int
    {
        $wildcards = PermissionWildcard::active()->autoExpand()->get();

        $totalCount = 0;

        foreach ($wildcards as $wildcard) {
            $count = $this->rebuildExpansions($wildcard);
            $totalCount += $count;
        }

        Log::info("Auto-expanded all wildcards", [
            'wildcards_count' => $wildcards->count(),
            'total_permissions' => $totalCount,
        ]);

        return $totalCount;
    }

    /**
     * Expand macro pattern (custom logic)
     *
     * @param string $pattern
     * @return Collection<Permission>
     */
    private function expandMacro(string $pattern): Collection
    {
        // Handle specific macro patterns
        return match($pattern) {
            'shops.read' => Permission::whereIn('slug', [
                'shops.list', 'shops.view', 'shops.read'
            ])->get(),

            'shops.write' => Permission::whereIn('slug', [
                'shops.create', 'shops.update', 'shops.delete'
            ])->get(),

            'shops.admin' => Permission::where('slug', 'like', 'shops.%')->get(),

            // Add more macros as needed
            default => collect(),
        };
    }

    /**
     * Check if permission matches macro pattern
     *
     * @param Permission $permission
     * @param string $pattern
     * @return bool
     */
    private function matchesMacro(Permission $permission, string $pattern): bool
    {
        $macroPermissions = $this->expandMacro($pattern);

        return $macroPermissions->contains('id', $permission->id);
    }
}
