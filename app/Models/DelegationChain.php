<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $delegation_id
 * @property int|null $parent_delegation_id
 * @property int $depth
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>|null $chain_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PermissionDelegation $delegation
 * @property-read \App\Models\PermissionDelegation|null $parentDelegation
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain whereChainPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain whereDelegationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain whereParentDelegationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DelegationChain whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DelegationChain extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'delegation_chains';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'delegation_id',
        'parent_delegation_id',
        'depth',
        'chain_path',
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
            'delegation_id' => 'integer',
            'parent_delegation_id' => 'integer',
            'depth' => 'integer',

            // JSON columns
            'chain_path' => AsArrayObject::class,

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the delegation that owns this chain entry.
     */
    public function delegation(): BelongsTo
    {
        return $this->belongsTo(PermissionDelegation::class, 'delegation_id');
    }

    /**
     * Get the parent delegation in the chain.
     */
    public function parentDelegation(): BelongsTo
    {
        return $this->belongsTo(PermissionDelegation::class, 'parent_delegation_id');
    }

    /**
     * Get all descendants of a delegation.
     */
    public static function getDescendants(int $delegationId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('parent_delegation_id', $delegationId)
            ->with('delegation')
            ->get();
    }

    /**
     * Get the full chain for a delegation.
     */
    public static function getChain(int $delegationId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('delegation_id', $delegationId)
            ->orderBy('depth')
            ->with(['delegation', 'parentDelegation'])
            ->get();
    }

    /**
     * Check if delegation depth exceeds maximum.
     */
    public static function exceedsMaxDepth(int $delegationId, int $maxDepth): bool
    {
        $currentDepth = static::where('delegation_id', $delegationId)
            ->max('depth');

        return $currentDepth >= $maxDepth;
    }

    /**
     * Get maximum depth for a delegation.
     */
    public static function getMaxDepth(int $delegationId): int
    {
        return static::where('delegation_id', $delegationId)
            ->max('depth') ?? 0;
    }
}
