<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * PermissionRequest Model
 * 
 * Permission request/approval workflow
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property int|null $scope_id
 * @property string $reason
 * @property string $status
 * @property \Illuminate\Support\Carbon $requested_at
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $review_comment
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\Permission $permission
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\Scope|null $scope
 * @property-read \App\Models\User $user
 * @method static Builder<static>|PermissionRequest approved()
 * @method static Builder<static>|PermissionRequest newModelQuery()
 * @method static Builder<static>|PermissionRequest newQuery()
 * @method static Builder<static>|PermissionRequest pending()
 * @method static Builder<static>|PermissionRequest query()
 * @method static Builder<static>|PermissionRequest rejected()
 * @method static Builder<static>|PermissionRequest whereCreatedAt($value)
 * @method static Builder<static>|PermissionRequest whereId($value)
 * @method static Builder<static>|PermissionRequest whereMetadata($value)
 * @method static Builder<static>|PermissionRequest wherePermissionId($value)
 * @method static Builder<static>|PermissionRequest whereReason($value)
 * @method static Builder<static>|PermissionRequest whereRequestedAt($value)
 * @method static Builder<static>|PermissionRequest whereReviewComment($value)
 * @method static Builder<static>|PermissionRequest whereReviewedAt($value)
 * @method static Builder<static>|PermissionRequest whereReviewedBy($value)
 * @method static Builder<static>|PermissionRequest whereScopeId($value)
 * @method static Builder<static>|PermissionRequest whereStatus($value)
 * @method static Builder<static>|PermissionRequest whereUpdatedAt($value)
 * @method static Builder<static>|PermissionRequest whereUserId($value)
 * @mixin \Eloquent
 */
class PermissionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'permission_id',
        'scope_id',
        'reason',
        'status',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'review_comment',
        'metadata',
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
            'user_id' => 'integer',
            'permission_id' => 'integer',
            'scope_id' => 'integer',
            'reviewed_by' => 'integer',

            // JSON columns
            'metadata' => AsArrayObject::class,

            // DateTime columns
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Alias for consistency
    public function approver(): BelongsTo
    {
        return $this->reviewer();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Approve this request
     */
    public function approve(User $reviewer, string $comment = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_comment' => $comment,
        ]);
    }

    /**
     * Reject this request
     */
    public function reject(User $reviewer, string $comment = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_comment' => $comment,
        ]);
    }
}
