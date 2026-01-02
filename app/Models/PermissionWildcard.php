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
 * @property int $id
 * @property string $pattern
 * @property string|null $description
 * @property string $pattern_type
 * @property string|null $icon
 * @property string $color
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $auto_expand
 * @property \Carbon\Carbon|null $last_expanded_at
 * @property int $permissions_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Collection<Permission> $permissions
 * @property-read Collection<PermissionTemplate> $templates
 *
 * @author Noflaye Box Team
 * @version 1.0.0
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
