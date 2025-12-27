<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * PermissionAuditLog Model
 *
 * Comprehensive audit trail for permission changes
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string|null $user_email
 * @property string $action
 * @property string $permission_slug
 * @property string|null $permission_name
 * @property string $source
 * @property int|null $source_id
 * @property string|null $source_name
 * @property int|null $scope_id
 * @property int|null $performed_by
 * @property string|null $performed_by_name
 * @property string|null $reason
 * @property array|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 *
 * @property-read User|null $user
 * @property-read Scope|null $scope
 * @property-read User|null $performer
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionAuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'permission_slug',
        'permission_name',
        'source',
        'source_id',
        'source_name',
        'scope_id',
        'performed_by',
        'performed_by_name',
        'reason',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeByPermission(Builder $query, string $permissionSlug): Builder
    {
        return $query->where('permission_slug', $permissionSlug);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Get action as AuditAction enum
     */
    public function getActionEnum(): ?AuditAction
    {
        try {
            return AuditAction::from($this->action);
        } catch (\ValueError $e) {
            return null;
        }
    }
}
