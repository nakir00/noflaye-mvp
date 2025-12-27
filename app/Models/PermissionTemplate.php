<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * PermissionTemplate Model
 *
 * Templates for grouping permissions (replaces roles)
 * Supports hierarchy, wildcards, and versioning
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
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read PermissionTemplate|null $parent
 * @property-read Collection<PermissionTemplate> $children
 * @property-read Collection<Permission> $permissions
 * @property-read Collection<PermissionWildcard> $wildcards
 * @property-read Collection<User> $users
 * @property-read Collection<PermissionTemplateVersion> $versions
 * @property-read Scope|null $scope
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionTemplate extends Model
{
    use SoftDeletes;

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

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'auto_sync_users' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $with = ['permissions', 'wildcards'];

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
        return $this->belongsToMany(Permission::class, 'template_permissions')
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
}
