<?php

namespace App\Services\Permissions;

use App\Models\PermissionTemplate;
use App\Models\PermissionTemplateVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TemplateVersionManager Service
 *
 * Manage permission template versioning and publishing
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class TemplateVersionManager
{
    /**
     * Create new version snapshot
     *
     * @param  PermissionTemplate  $template  Template to version
     * @param  User  $user  User creating version
     * @param  string|null  $versionName  Optional version name
     * @param  string|null  $changelog  Optional changelog
     */
    public function createVersion(
        PermissionTemplate $template,
        User $user,
        ?string $versionName = null,
        ?string $changelog = null
    ): PermissionTemplateVersion {
        return DB::transaction(function () use ($template, $user, $versionName, $changelog) {
            // Get next version number
            $latestVersion = PermissionTemplateVersion::where('template_id', $template->id)
                ->max('version');
            $nextVersion = ($latestVersion ?? 0) + 1;

            // Create snapshots
            $permissionsSnapshot = $template->permissions->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'slug' => $perm->slug,
                    'name' => $perm->name,
                    'source' => $perm->pivot->source,
                ];
            })->toArray();

            $wildcardsSnapshot = $template->wildcards->map(function ($wildcard) {
                return [
                    'id' => $wildcard->id,
                    'pattern' => $wildcard->pattern,
                    'description' => $wildcard->description,
                ];
            })->toArray();

            // Create version
            $version = PermissionTemplateVersion::create([
                'template_id' => $template->id,
                'version' => $nextVersion,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'parent_id' => $template->parent_id,
                'scope_id' => $template->scope_id,
                'color' => $template->color,
                'icon' => $template->icon,
                'level' => $template->level,
                'permissions_snapshot' => $permissionsSnapshot,
                'wildcards_snapshot' => $wildcardsSnapshot,
                'version_name' => $versionName,
                'changelog' => $changelog,
                'is_stable' => false,
                'is_published' => false,
                'created_by' => $user->id,
            ]);

            Log::info('Template version created', [
                'version_id' => $version->id,
                'template_id' => $template->id,
                'version' => $nextVersion,
                'created_by' => $user->id,
            ]);

            return $version;
        });
    }

    /**
     * Publish version
     *
     * @param  PermissionTemplateVersion  $version  Version to publish
     * @param  User  $user  User publishing version
     */
    public function publish(PermissionTemplateVersion $version, User $user): bool
    {
        if ($version->is_published) {
            return false; // Already published
        }

        $result = $version->update([
            'is_published' => true,
            'is_stable' => true,
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        if ($result) {
            Log::info('Template version published', [
                'version_id' => $version->id,
                'template_id' => $version->template_id,
                'version' => $version->version,
                'published_by' => $user->id,
            ]);
        }

        return $result;
    }

    /**
     * Restore template to specific version
     *
     * @param  PermissionTemplateVersion  $version  Version to restore
     */
    public function restore(PermissionTemplateVersion $version): bool
    {
        return DB::transaction(function () use ($version) {
            $template = $version->template;

            // Restore template metadata
            $template->update([
                'name' => $version->name,
                'slug' => $version->slug,
                'description' => $version->description,
                'parent_id' => $version->parent_id,
                'scope_id' => $version->scope_id,
                'color' => $version->color,
                'icon' => $version->icon,
            ]);

            // Restore permissions
            $template->permissions()->detach();
            foreach ($version->permissions_snapshot as $perm) {
                $template->permissions()->attach($perm['id'], [
                    'source' => $perm['source'],
                ]);
            }

            // Restore wildcards
            $template->wildcards()->detach();
            foreach ($version->wildcards_snapshot as $wildcard) {
                $template->wildcards()->attach($wildcard['id']);
            }

            Log::info('Template restored to version', [
                'template_id' => $template->id,
                'version_id' => $version->id,
                'version' => $version->version,
            ]);

            return true;
        });
    }

    /**
     * Compare two versions
     *
     * @param  PermissionTemplateVersion  $v1  First version
     * @param  PermissionTemplateVersion  $v2  Second version
     * @return array Diff result
     */
    public function compareVersions(PermissionTemplateVersion $v1, PermissionTemplateVersion $v2): array
    {
        $v1Perms = collect($v1->permissions_snapshot)->pluck('slug')->sort()->values();
        $v2Perms = collect($v2->permissions_snapshot)->pluck('slug')->sort()->values();

        $added = $v2Perms->diff($v1Perms);
        $removed = $v1Perms->diff($v2Perms);

        return [
            'version_1' => $v1->version,
            'version_2' => $v2->version,
            'permissions_added' => $added->values()->toArray(),
            'permissions_removed' => $removed->values()->toArray(),
            'permissions_count_change' => $v2Perms->count() - $v1Perms->count(),
        ];
    }

    /**
     * Rollback template to previous version
     *
     * @param  PermissionTemplate  $template  Template to rollback
     * @param  int  $steps  Number of versions to go back
     */
    public function rollback(PermissionTemplate $template, int $steps = 1): ?PermissionTemplateVersion
    {
        $currentVersion = PermissionTemplateVersion::where('template_id', $template->id)
            ->latest('version')
            ->first();

        if (! $currentVersion) {
            return null;
        }

        $targetVersion = $currentVersion->version - $steps;

        if ($targetVersion < 1) {
            return null;
        }

        $version = PermissionTemplateVersion::where('template_id', $template->id)
            ->where('version', $targetVersion)
            ->first();

        if ($version) {
            $this->restore($version);
        }

        return $version;
    }
}
