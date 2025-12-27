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
 * @property int $id
 * @property string $scopable_type
 * @property int $scopable_id
 * @property string $scope_key
 * @property string|null $name
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Model $scopable
 *
 * @author Noflaye Box Team
 * @version 1.0.0
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

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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
