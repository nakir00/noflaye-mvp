<?php

namespace App\Models;

use App\Enums\WildcardPattern;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * PermissionWildcard Model
 * 
 * Wildcard patterns for automatic permission expansion
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 * @property int $id
 * @property string $pattern
 * @property string|null $description
 * @property WildcardPattern $pattern_type
 * @property string|null $icon
 * @property string $color
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $auto_expand
 * @property \Illuminate\Support\Carbon|null $last_expanded_at
 * @property-read int|null $permissions_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionTemplate> $templates
 * @property-read int|null $templates_count
 * @method static Builder<static>|PermissionWildcard active()
 * @method static Builder<static>|PermissionWildcard autoExpand()
 * @method static Builder<static>|PermissionWildcard newModelQuery()
 * @method static Builder<static>|PermissionWildcard newQuery()
 * @method static Builder<static>|PermissionWildcard query()
 * @method static Builder<static>|PermissionWildcard whereAutoExpand($value)
 * @method static Builder<static>|PermissionWildcard whereColor($value)
 * @method static Builder<static>|PermissionWildcard whereCreatedAt($value)
 * @method static Builder<static>|PermissionWildcard whereDescription($value)
 * @method static Builder<static>|PermissionWildcard whereIcon($value)
 * @method static Builder<static>|PermissionWildcard whereId($value)
 * @method static Builder<static>|PermissionWildcard whereIsActive($value)
 * @method static Builder<static>|PermissionWildcard whereLastExpandedAt($value)
 * @method static Builder<static>|PermissionWildcard wherePattern($value)
 * @method static Builder<static>|PermissionWildcard wherePatternType($value)
 * @method static Builder<static>|PermissionWildcard wherePermissionsCount($value)
 * @method static Builder<static>|PermissionWildcard whereSortOrder($value)
 * @method static Builder<static>|PermissionWildcard whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PermissionWildcard extends Model
{
    protected $fillable = [
        'pattern',
        'description',
        'pattern_type',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'auto_expand',
        'last_expanded_at',
        'permissions_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Enum columns
            'pattern_type' => WildcardPattern::class,

            // Boolean columns
            'is_active' => 'boolean',
            'auto_expand' => 'boolean',

            // Integer columns
            'sort_order' => 'integer',
            'permissions_count' => 'integer',

            // DateTime columns
            'last_expanded_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'wildcard_permissions')
            ->withPivot('is_auto_generated', 'expanded_at')
            ->withTimestamps();
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(PermissionTemplate::class, 'template_wildcards')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAutoExpand(Builder $query): Builder
    {
        return $query->where('auto_expand', true);
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Get pattern as WildcardPattern enum (if exists)
     */
    public function getPatternEnum(): ?WildcardPattern
    {
        try {
            return WildcardPattern::from($this->pattern);
        } catch (\ValueError $e) {
            return null;
        }
    }

    /**
     * Mark as expanded
     */
    public function markAsExpanded(int $count): bool
    {
        return $this->update([
            'last_expanded_at' => now(),
            'permissions_count' => $count,
        ]);
    }
}
