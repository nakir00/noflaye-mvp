<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogsActivity;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

/**
 * PermissionTemplate Model
 * 
 * Templates for grouping permissions (replaces roles)
 * Supports hierarchy, wildcards, and versioning
 *
 * @author Noflaye Box Team
 * @version 1.0.0
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
 * @property-read PermissionTemplate|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Scope|null $scope
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionTemplateVersion> $versions
 * @property-read int|null $versions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionWildcard> $wildcards
 * @property-read int|null $wildcards_count
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
 * @mixin \Eloquent
 */
class PermissionTemplate extends Model
{
    use SoftDeletes;
    use LogsActivityTrait;

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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PermissionTemplate::class, 'parent_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'template_permissions', 'template_id', 'permission_id')
            ->withPivot('source', 'wildcard_id', 'sort_order')
            ->withTimestamps();
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
        return $this->hasMany(PermissionTemplateVersion::class, 'template_id');
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
     * Get all permissions including inherited from parent
     */
    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;

        if ($this->parent_id) {
            $permissions = $permissions->merge($this->parent->getAllPermissions());
        }

        return $permissions->unique('id');
    }

    /**
     * Sync permissions to all users with auto_sync enabled
     */
    public function syncUsers(): int
    {
        if (!$this->auto_sync_users) {
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
