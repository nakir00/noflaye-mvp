<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PermissionDelegation Model
 *
 * Temporary permission delegation with re-delegation support
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 *
 * @property int $id
 * @property int $delegator_id
 * @property string $delegator_name
 * @property int $delegatee_id
 * @property string $delegatee_name
 * @property int $permission_id
 * @property string $permission_slug
 * @property int|null $scope_id
 * @property \Illuminate\Support\Carbon $valid_from
 * @property \Illuminate\Support\Carbon $valid_until
 * @property bool $can_redelegate
 * @property int $max_redelegation_depth
 * @property string|null $reason
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property int|null $revoked_by
 * @property string|null $revocation_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DelegationChain> $chain
 * @property-read int|null $chain_count
 * @property-read \App\Models\User $delegatee
 * @property-read \App\Models\User $delegator
 * @property-read \App\Models\Permission $permission
 * @property-read \App\Models\User|null $revoker
 * @property-read \App\Models\Scope|null $scope
 *
 * @method static Builder<static>|PermissionDelegation active()
 * @method static Builder<static>|PermissionDelegation expired()
 * @method static Builder<static>|PermissionDelegation newModelQuery()
 * @method static Builder<static>|PermissionDelegation newQuery()
 * @method static Builder<static>|PermissionDelegation query()
 * @method static Builder<static>|PermissionDelegation revoked()
 * @method static Builder<static>|PermissionDelegation whereCanRedelegate($value)
 * @method static Builder<static>|PermissionDelegation whereCreatedAt($value)
 * @method static Builder<static>|PermissionDelegation whereDelegateeId($value)
 * @method static Builder<static>|PermissionDelegation whereDelegateeName($value)
 * @method static Builder<static>|PermissionDelegation whereDelegatorId($value)
 * @method static Builder<static>|PermissionDelegation whereDelegatorName($value)
 * @method static Builder<static>|PermissionDelegation whereId($value)
 * @method static Builder<static>|PermissionDelegation whereMaxRedelegationDepth($value)
 * @method static Builder<static>|PermissionDelegation whereMetadata($value)
 * @method static Builder<static>|PermissionDelegation wherePermissionId($value)
 * @method static Builder<static>|PermissionDelegation wherePermissionSlug($value)
 * @method static Builder<static>|PermissionDelegation whereReason($value)
 * @method static Builder<static>|PermissionDelegation whereRevocationReason($value)
 * @method static Builder<static>|PermissionDelegation whereRevokedAt($value)
 * @method static Builder<static>|PermissionDelegation whereRevokedBy($value)
 * @method static Builder<static>|PermissionDelegation whereScopeId($value)
 * @method static Builder<static>|PermissionDelegation whereUpdatedAt($value)
 * @method static Builder<static>|PermissionDelegation whereValidFrom($value)
 * @method static Builder<static>|PermissionDelegation whereValidUntil($value)
 *
 * @mixin \Eloquent
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Integer columns
            'delegator_id' => 'integer',
            'delegatee_id' => 'integer',
            'permission_id' => 'integer',
            'scope_id' => 'integer',
            'max_redelegation_depth' => 'integer',
            'revoked_by' => 'integer',

            // Boolean columns
            'can_redelegate' => 'boolean',

            // DateTime columns
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'revoked_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',

            // JSON columns
            'metadata' => AsArrayObject::class,
        ];
    }

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
    public function revoke(User $revokedBy, ?string $reason = null): bool
    {
        return $this->update([
            'revoked_at' => now(),
            'revoked_by' => $revokedBy->id,
            'revocation_reason' => $reason,
        ]);
    }
}
