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
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property int|null $scope_id
 * @property string $reason
 * @property string $status
 * @property \Carbon\Carbon $requested_at
 * @property \Carbon\Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $review_comment
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read Permission $permission
 * @property-read Scope|null $scope
 * @property-read User|null $reviewer
 *
 * @author Noflaye Box Team
 * @version 1.0.0
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
