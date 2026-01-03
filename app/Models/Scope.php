<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scope Model
 * 
 * Unified scope management for permissions, templates, and groups
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 * @property int $id
 * @property string $scopable_type
 * @property int $scopable_id
 * @property string $scope_key
 * @property string|null $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|\Eloquent $scopable
 * @method static Builder<static>|Scope active()
 * @method static Builder<static>|Scope byKey(string $key)
 * @method static Builder<static>|Scope byType(string $type)
 * @method static Builder<static>|Scope newModelQuery()
 * @method static Builder<static>|Scope newQuery()
 * @method static Builder<static>|Scope onlyTrashed()
 * @method static Builder<static>|Scope query()
 * @method static Builder<static>|Scope whereCreatedAt($value)
 * @method static Builder<static>|Scope whereDeletedAt($value)
 * @method static Builder<static>|Scope whereId($value)
 * @method static Builder<static>|Scope whereIsActive($value)
 * @method static Builder<static>|Scope whereName($value)
 * @method static Builder<static>|Scope whereScopableId($value)
 * @method static Builder<static>|Scope whereScopableType($value)
 * @method static Builder<static>|Scope whereScopeKey($value)
 * @method static Builder<static>|Scope whereUpdatedAt($value)
 * @method static Builder<static>|Scope withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Scope withoutTrashed()
 * @mixin \Eloquent
 */
class Scope extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'scopable_type',
        'scopable_id',
        'scope_key',
        'name',
        'is_active',
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
            'scopable_id' => 'integer',

            // Boolean columns
            'is_active' => 'boolean',

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the owning scopable model (Shop, Kitchen, etc.)
     */
    public function scopable(): MorphTo
    {
        return $this->morphTo();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to only active scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to specific scopable type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('scopable_type', $type);
    }

    /**
     * Scope to specific scope key
     */
    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('scope_key', $key);
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Deactivate this scope
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Activate this scope
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Get display name (name or generated from key)
     */
    public function getDisplayName(): string
    {
        return $this->name ?? $this->scope_key;
    }
}
