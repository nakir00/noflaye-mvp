<?php

namespace App\Models;

use App\Traits\HasHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

/**
 * PermissionTemplate Model
 *
 * Templates for grouping permissions (replaces roles)
 * Supports hierarchy with full ancestry/descendant traversal and permission inheritance with override
 *
 * Hierarchy:
 * - parent() → BelongsTo → direct parent via parent_id
 * - parents() → BelongsToMany → all ancestors via hierarchy table (ordered by depth)
 * - children() → HasMany → direct children via parent_id (ordered by sort_order)
 * - allChildren() → BelongsToMany → all descendants via hierarchy table (ordered by depth, sort_order)
 *
 * Permission Inheritance:
 * - Additive: child inherits all permissions from parents
 * - Override: child can deny specific permissions to block inheritance
 *
 * @author Noflaye Box Team
 *
 * @version 2.0.0
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property int|null $scope_id
 * @property string $color
 * @property string $icon
 * @property int $level
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_system
 * @property bool $auto_sync_users
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PermissionTemplate> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PermissionTemplate> $allChildren
 * @property-read int|null $all_children_count
 * @property-read PermissionTemplate|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PermissionTemplate> $parents
 * @property-read int|null $parents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $grantedPermissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $deniedPermissions
 * @property-read \App\Models\Scope|null $scope
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionTemplateVersion> $versions
 * @property-read int|null $versions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionWildcard> $wildcards
 * @property-read int|null $wildcards_count
 *
 * @method static Builder<static>|PermissionTemplate active()
 * @method static Builder<static>|PermissionTemplate newModelQuery()
 * @method static Builder<static>|PermissionTemplate newQuery()
 * @method static Builder<static>|PermissionTemplate onlyTrashed()
 * @method static Builder<static>|PermissionTemplate published()
 * @method static Builder<static>|PermissionTemplate query()
 * @method static Builder<static>|PermissionTemplate root()
 * @method static Builder<static>|PermissionTemplate whereAutoSyncUsers($value)
 * @method static Builder<static>|PermissionTemplate whereColor($value)
 * @method static Builder<static>|PermissionTemplate whereCreatedAt($value)
 * @method static Builder<static>|PermissionTemplate whereDeletedAt($value)
 * @method static Builder<static>|PermissionTemplate whereDescription($value)
 * @method static Builder<static>|PermissionTemplate whereIcon($value)
 * @method static Builder<static>|PermissionTemplate whereId($value)
 * @method static Builder<static>|PermissionTemplate whereIsActive($value)
 * @method static Builder<static>|PermissionTemplate whereIsSystem($value)
 * @method static Builder<static>|PermissionTemplate whereLevel($value)
 * @method static Builder<static>|PermissionTemplate whereName($value)
 * @method static Builder<static>|PermissionTemplate whereParentId($value)
 * @method static Builder<static>|PermissionTemplate whereScopeId($value)
 * @method static Builder<static>|PermissionTemplate whereSlug($value)
 * @method static Builder<static>|PermissionTemplate whereSortOrder($value)
 * @method static Builder<static>|PermissionTemplate whereUpdatedAt($value)
 * @method static Builder<static>|PermissionTemplate withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|PermissionTemplate withoutTrashed()
 *
 * @mixin \Eloquent
 */
class PermissionTemplate extends Model
{
    use HasHierarchy;
    use LogsActivityTrait;
    use SoftDeletes;

    /**
     * The hierarchy table for closure table relationships.
     */
    protected string $hierarchyTable = 'permission_template_hierarchy';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'scope_id',
        'color',
        'icon',
        'level',
        'sort_order',
        'is_active',
        'is_system',
        'auto_sync_users',
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
            'scope_id' => 'integer',
            'level' => 'integer',
            'sort_order' => 'integer',

            // Boolean columns
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'auto_sync_users' => 'boolean',

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Disabled eager loading - enable only when needed
    // protected $with = ['permissions', 'wildcards'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    // Note: parent(), parents(), children(), allChildren() are provided by HasHierarchy trait

    /**
     * Get all permissions (both granted and denied).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'template_permissions', 'permission_template_id', 'permission_id')
            ->withPivot('source', 'permission_type', 'wildcard_id', 'sort_order')
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
     * These override inherited permissions.
     */
    public function deniedPermissions(): BelongsToMany
    {
        return $this->permissions()->wherePivot('permission_type', 'deny');
    }

    public function wildcards(): BelongsToMany
    {
        return $this->belongsToMany(PermissionWildcard::class, 'template_wildcards')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_templates')
            ->withPivot('scope_id', 'template_version', 'auto_upgrade', 'auto_sync', 'valid_from', 'valid_until')
            ->withTimestamps();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PermissionTemplateVersion::class, 'permission_template_id');
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_system', false);
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Get all effective permissions including inherited from all ancestors.
     * Respects override system: denied permissions block inherited ones.
     *
     * Algorithm:
     * 1. Get all granted permissions from this template
     * 2. Get all denied permission IDs from this template
     * 3. Get all ancestor templates (from root to direct parent)
     * 4. Collect inherited permissions, excluding denied ones
     *
     * @return Collection<Permission>
     */
    public function getAllPermissions(): Collection
    {
        // Get own granted permissions
        $grantedPermissions = $this->grantedPermissions()->get();

        // Get own denied permission IDs (these block inheritance)
        $deniedPermissionIds = $this->deniedPermissions()->pluck('permissions.id')->toArray();

        // Get inherited permissions from all ancestors
        $inheritedPermissions = collect();

        // Load ancestors from root to closest parent (for proper override cascade)
        $ancestors = $this->getAncestorsFromRoot();

        foreach ($ancestors as $ancestor) {
            // Get ancestor's granted permissions
            $ancestorPermissions = $ancestor->grantedPermissions()->get();

            // Add to inherited, will be filtered later
            $inheritedPermissions = $inheritedPermissions->merge($ancestorPermissions);
        }

        // Filter out denied permissions from inherited
        $inheritedPermissions = $inheritedPermissions->filter(
            fn ($permission) => ! in_array($permission->id, $deniedPermissionIds)
        );

        // Merge own granted + inherited (filtered)
        return $grantedPermissions
            ->merge($inheritedPermissions)
            ->unique('id')
            ->values();
    }

    /**
     * Get only the permissions directly assigned to this template (not inherited).
     *
     * @return Collection<Permission>
     */
    public function getOwnPermissions(): Collection
    {
        return $this->grantedPermissions()->get();
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

        $inheritedPermissions = collect();
        $ancestors = $this->getAncestorsFromRoot();

        foreach ($ancestors as $ancestor) {
            $ancestorPermissions = $ancestor->grantedPermissions()->get();
            $inheritedPermissions = $inheritedPermissions->merge($ancestorPermissions);
        }

        // Filter out denied and own permissions
        return $inheritedPermissions
            ->filter(fn ($permission) => ! in_array($permission->id, $deniedPermissionIds))
            ->filter(fn ($permission) => ! in_array($permission->id, $ownPermissionIds))
            ->unique('id')
            ->values();
    }

    /**
     * Check if this template has a specific permission (including inherited).
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
     * Grant a permission to this template.
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
     * Deny a permission (override inherited permission).
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
     * Remove a permission grant or deny from this template.
     */
    public function revokePermission(Permission|int $permission): void
    {
        $permissionId = $permission instanceof Permission ? $permission->id : $permission;
        $this->permissions()->detach($permissionId);
    }

    /**
     * Sync permissions to all users with auto_sync enabled
     */
    public function syncUsers(): int
    {
        if (! $this->auto_sync_users) {
            return 0;
        }

        $users = $this->users()->wherePivot('auto_sync', true)->get();

        foreach ($users as $user) {
            // Logic handled by service layer
        }

        return $users->count();
    }

    // ========================================
    // ACTIVITY LOG CONFIGURATION
    // ========================================

    /**
     * Get the log name for activity logging
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'is_active', 'is_system', 'parent_id', 'level', 'sort_order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
