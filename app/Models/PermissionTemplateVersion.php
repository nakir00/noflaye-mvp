<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * PermissionTemplateVersion Model
 * 
 * Version control for permission templates with snapshots
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 * @property int $id
 * @property int $template_id
 * @property int $version
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property int|null $scope_id
 * @property string|null $color
 * @property string|null $icon
 * @property int $level
 * @property array<array-key, mixed> $permissions_snapshot
 * @property array<array-key, mixed>|null $wildcards_snapshot
 * @property string|null $version_name
 * @property string|null $changelog
 * @property bool $is_stable
 * @property bool $is_published
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $published_by
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $publisher
 * @property-read \App\Models\PermissionTemplate $template
 * @method static Builder<static>|PermissionTemplateVersion latest()
 * @method static Builder<static>|PermissionTemplateVersion newModelQuery()
 * @method static Builder<static>|PermissionTemplateVersion newQuery()
 * @method static Builder<static>|PermissionTemplateVersion published()
 * @method static Builder<static>|PermissionTemplateVersion query()
 * @method static Builder<static>|PermissionTemplateVersion stable()
 * @method static Builder<static>|PermissionTemplateVersion whereChangelog($value)
 * @method static Builder<static>|PermissionTemplateVersion whereColor($value)
 * @method static Builder<static>|PermissionTemplateVersion whereCreatedAt($value)
 * @method static Builder<static>|PermissionTemplateVersion whereCreatedBy($value)
 * @method static Builder<static>|PermissionTemplateVersion whereDescription($value)
 * @method static Builder<static>|PermissionTemplateVersion whereIcon($value)
 * @method static Builder<static>|PermissionTemplateVersion whereId($value)
 * @method static Builder<static>|PermissionTemplateVersion whereIsPublished($value)
 * @method static Builder<static>|PermissionTemplateVersion whereIsStable($value)
 * @method static Builder<static>|PermissionTemplateVersion whereLevel($value)
 * @method static Builder<static>|PermissionTemplateVersion whereName($value)
 * @method static Builder<static>|PermissionTemplateVersion whereParentId($value)
 * @method static Builder<static>|PermissionTemplateVersion wherePermissionsSnapshot($value)
 * @method static Builder<static>|PermissionTemplateVersion wherePublishedAt($value)
 * @method static Builder<static>|PermissionTemplateVersion wherePublishedBy($value)
 * @method static Builder<static>|PermissionTemplateVersion whereScopeId($value)
 * @method static Builder<static>|PermissionTemplateVersion whereSlug($value)
 * @method static Builder<static>|PermissionTemplateVersion whereTemplateId($value)
 * @method static Builder<static>|PermissionTemplateVersion whereVersion($value)
 * @method static Builder<static>|PermissionTemplateVersion whereVersionName($value)
 * @method static Builder<static>|PermissionTemplateVersion whereWildcardsSnapshot($value)
 * @mixin \Eloquent
 */
class PermissionTemplateVersion extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'template_id',
        'version',
        'name',
        'slug',
        'description',
        'parent_id',
        'scope_id',
        'color',
        'icon',
        'level',
        'permissions_snapshot',
        'wildcards_snapshot',
        'version_name',
        'changelog',
        'is_stable',
        'is_published',
        'created_by',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'level' => 'integer',
        'permissions_snapshot' => 'array',
        'wildcards_snapshot' => 'array',
        'is_stable' => 'boolean',
        'is_published' => 'boolean',
        'created_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function template(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeStable(Builder $query): Builder
    {
        return $query->where('is_stable', true);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('version', 'desc');
    }
}
