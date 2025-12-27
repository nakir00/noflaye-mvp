<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * PermissionDelegation Model
 *
 * Temporary permission delegation with re-delegation support
 *
 * @property int $id
 * @property int $delegator_id
 * @property string $delegator_name
 * @property int $delegatee_id
 * @property string $delegatee_name
 * @property int $permission_id
 * @property string $permission_slug
 * @property int|null $scope_id
 * @property \Carbon\Carbon $valid_from
 * @property \Carbon\Carbon $valid_until
 * @property bool $can_redelegate
 * @property int $max_redelegation_depth
 * @property string|null $reason
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $revoked_at
 * @property int|null $revoked_by
 * @property string|null $revocation_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $delegator
 * @property-read User $delegatee
 * @property-read Permission $permission
 * @property-read Scope|null $scope
 * @property-read User|null $revoker
 * @property-read Collection $chain
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionDelegation extends Model
{
    protected $fillable = [
        'delegator_id',
        'delegator_name',
        'delegatee_id',
        'delegatee_name',
        'permission_id',
        'permission_slug',
        'scope_id',
        'valid_from',
        'valid_until',
        'can_redelegate',
        'max_redelegation_depth',
        'reason',
        'metadata',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'can_redelegate' => 'boolean',
        'max_redelegation_depth' => 'integer',
        'metadata' => 'array',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegatee_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function chain(): HasMany
    {
        return $this->hasMany(DelegationChain::class, 'delegation_id');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where('valid_until', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where('valid_until', '<=', now());
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->whereNotNull('revoked_at');
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Check if delegation is currently active
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at) &&
               $this->valid_until > now();
    }

    /**
     * Check if can be re-delegated
     */
    public function canRedelegate(): bool
    {
        return $this->can_redelegate &&
               $this->isActive();
    }

    /**
     * Revoke this delegation
     */
    public function revoke(User $revokedBy, string $reason = null): bool
    {
        return $this->update([
            'revoked_at' => now(),
            'revoked_by' => $revokedBy->id,
            'revocation_reason' => $reason,
        ]);
    }
}
