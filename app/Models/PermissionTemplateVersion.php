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
 * @property array $permissions_snapshot
 * @property array|null $wildcards_snapshot
 * @property string|null $version_name
 * @property string|null $changelog
 * @property bool $is_stable
 * @property bool $is_published
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $published_at
 * @property int|null $published_by
 *
 * @property-read PermissionTemplate $template
 * @property-read User|null $creator
 * @property-read User|null $publisher
 *
 * @author Noflaye Box Team
 * @version 1.0.0
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
