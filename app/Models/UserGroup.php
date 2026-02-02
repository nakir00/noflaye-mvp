<?php

namespace App\Models;

use App\Traits\HasHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * UserGroup Model
 *
 * Groups users for bulk permission management.
 * Supports hierarchy with full ancestry/descendant traversal and permission inheritance with override.
 *
 * Hierarchy:
 * - parent() → BelongsTo → direct parent via parent_id
 * - parents() → BelongsToMany → all ancestors via hierarchy table (ordered by depth)
 * - children() → HasMany → direct children via parent_id (ordered by sort_order)
 * - allChildren() → BelongsToMany → all descendants via hierarchy table (ordered by depth, sort_order)
 *
 * Permission Sources:
 * 1. Direct permissions on this group (grant/deny)
 * 2. Permissions from assigned template
 * 3. Inherited permissions from ancestor groups
 *
 * @author Noflaye Box Team
 *
 * @version 2.0.0
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $groupable_type
 * @property int $groupable_id
 * @property int|null $parent_id
 * @property int $level
 * @property int $sort_order
 * @property int|null $permission_template_id
 * @property bool $auto_sync_template
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserGroup> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserGroup> $allChildren
 * @property-read int|null $all_children_count
 * @property-read UserGroup|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserGroup> $parents
 * @property-read int|null $parents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $grantedPermissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $deniedPermissions
 * @property-read \App\Models\PermissionTemplate|null $template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\UserGroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereAutoSyncTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereGroupableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereGroupableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class UserGroup extends Model
{
    /** @use HasFactory<\Database\Factories\UserGroupFactory> */
    use HasFactory;

    use HasHierarchy;

    /**
     * The hierarchy table for closure table relationships.
     */
    protected string $hierarchyTable = 'user_group_hierarchy';

    protected $fillable = [
        'parent_id',
        'level',
        'sort_order',
        'permission_template_id',
        'auto_sync_template',
        'name',
        'slug',
        'description',
        'groupable_type',
        'groupable_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Integer columns
            'parent_id' => 'integer',
            'level' => 'integer',
            'sort_order' => 'integer',
            'permission_template_id' => 'integer',
            'groupable_id' => 'integer',

            // Boolean columns
            'auto_sync_template' => 'boolean',

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    // Note: parent(), parents(), children(), allChildren() are provided by HasHierarchy trait

    public function template(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'permission_template_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_members')
            ->withPivot('scope_id')
            ->withTimestamps();
    }

    /**
     * Get all permissions (both granted and denied).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_group_permissions')
            ->withPivot('permission_type', 'source')
            ->withTimestamps();
    }

    /**
     * Get only granted permissions (permission_type = 'grant').
     */
    public function grantedPermissions(): BelongsToMany
    {
        return $this->permissions()->wherePivot('permission_type', 'grant');
    }

    /**
     * Get only denied permissions (permission_type = 'deny').
     * These override inherited and template permissions.
     */
    public function deniedPermissions(): BelongsToMany
    {
        return $this->permissions()->wherePivot('permission_type', 'deny');
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Get all effective permissions from:
     * 1. Direct permissions on this group
     * 2. Permissions from assigned template
     * 3. Inherited permissions from ancestor groups
     *
     * Respects override system: denied permissions block inherited ones.
     *
     * @return Collection<Permission>
     */
    public function getAllPermissions(): Collection
    {
        // Get own granted permissions
        $grantedPermissions = $this->grantedPermissions()->get();

        // Get own denied permission IDs (these block inheritance)
        $deniedPermissionIds = $this->deniedPermissions()->pluck('permissions.id')->toArray();

        // Get permissions from assigned template
        $templatePermissions = collect();
        if ($this->permission_template_id && $this->template) {
            $templatePermissions = $this->template->getAllPermissions();
        }

        // Get inherited permissions from all ancestors
        $inheritedPermissions = collect();
        $ancestors = $this->getAncestorsFromRoot();

        foreach ($ancestors as $ancestor) {
            // Get ancestor's direct granted permissions
            $ancestorPermissions = $ancestor->grantedPermissions()->get();
            $inheritedPermissions = $inheritedPermissions->merge($ancestorPermissions);

            // Get ancestor's template permissions
            if ($ancestor->permission_template_id && $ancestor->template) {
                $ancestorTemplatePermissions = $ancestor->template->getAllPermissions();
                $inheritedPermissions = $inheritedPermissions->merge($ancestorTemplatePermissions);
            }
        }

        // Merge all sources and filter out denied
        $allPermissions = $grantedPermissions
            ->merge($templatePermissions)
            ->merge($inheritedPermissions)
            ->filter(fn ($permission) => ! in_array($permission->id, $deniedPermissionIds))
            ->unique('id')
            ->values();

        return $allPermissions;
    }

    /**
     * Get only the permissions directly assigned to this group (not inherited or from template).
     *
     * @return Collection<Permission>
     */
    public function getOwnPermissions(): Collection
    {
        return $this->grantedPermissions()->get();
    }

    /**
     * Get permissions from the assigned template only.
     *
     * @return Collection<Permission>
     */
    public function getTemplatePermissions(): Collection
    {
        if (! $this->permission_template_id || ! $this->template) {
            return collect();
        }

        return $this->template->getAllPermissions();
    }

    /**
     * Get only inherited permissions (from ancestors).
     *
     * @return Collection<Permission>
     */
    public function getInheritedPermissions(): Collection
    {
        $deniedPermissionIds = $this->deniedPermissions()->pluck('permissions.id')->toArray();
        $ownPermissionIds = $this->grantedPermissions()->pluck('permissions.id')->toArray();
        $templatePermissionIds = $this->getTemplatePermissions()->pluck('id')->toArray();

        $inheritedPermissions = collect();
        $ancestors = $this->getAncestorsFromRoot();

        foreach ($ancestors as $ancestor) {
            $ancestorPermissions = $ancestor->grantedPermissions()->get();
            $inheritedPermissions = $inheritedPermissions->merge($ancestorPermissions);

            if ($ancestor->permission_template_id && $ancestor->template) {
                $ancestorTemplatePermissions = $ancestor->template->getAllPermissions();
                $inheritedPermissions = $inheritedPermissions->merge($ancestorTemplatePermissions);
            }
        }

        // Filter out denied, own, and template permissions
        $excludedIds = array_merge($deniedPermissionIds, $ownPermissionIds, $templatePermissionIds);

        return $inheritedPermissions
            ->filter(fn ($permission) => ! in_array($permission->id, $excludedIds))
            ->unique('id')
            ->values();
    }

    /**
     * Check if this group has a specific permission (including inherited and template).
     */
    public function hasPermission(Permission|string|int $permission): bool
    {
        $permissionId = $permission instanceof Permission
            ? $permission->id
            : (is_string($permission)
                ? Permission::where('slug', $permission)->value('id')
                : $permission);

        if (! $permissionId) {
            return false;
        }

        return $this->getAllPermissions()->contains('id', $permissionId);
    }

    /**
     * Grant a permission to this group.
     */
    public function grantPermission(Permission|int $permission, string $source = 'direct'): void
    {
        $permissionId = $permission instanceof Permission ? $permission->id : $permission;

        $this->permissions()->syncWithoutDetaching([
            $permissionId => [
                'permission_type' => 'grant',
                'source' => $source,
            ],
        ]);
    }

    /**
     * Deny a permission (override inherited/template permission).
     */
    public function denyPermission(Permission|int $permission): void
    {
        $permissionId = $permission instanceof Permission ? $permission->id : $permission;

        $this->permissions()->syncWithoutDetaching([
            $permissionId => [
                'permission_type' => 'deny',
                'source' => 'direct',
            ],
        ]);
    }

    /**
     * Remove a permission grant or deny from this group.
     */
    public function revokePermission(Permission|int $permission): void
    {
        $permissionId = $permission instanceof Permission ? $permission->id : $permission;
        $this->permissions()->detach($permissionId);
    }
}
