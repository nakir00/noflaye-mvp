<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * PermissionAuditLog Model
 * 
 * Comprehensive audit trail for permission changes
 *
 * @author Noflaye Box Team
 * @version 1.0.0
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
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User|null $performer
 * @property-read \App\Models\Scope|null $scope
 * @property-read \App\Models\User|null $user
 * @method static Builder<static>|PermissionAuditLog byAction(string $action)
 * @method static Builder<static>|PermissionAuditLog byPermission(string $permissionSlug)
 * @method static Builder<static>|PermissionAuditLog byUser(int $userId)
 * @method static Builder<static>|PermissionAuditLog newModelQuery()
 * @method static Builder<static>|PermissionAuditLog newQuery()
 * @method static Builder<static>|PermissionAuditLog query()
 * @method static Builder<static>|PermissionAuditLog recent(int $days = 30)
 * @method static Builder<static>|PermissionAuditLog whereAction($value)
 * @method static Builder<static>|PermissionAuditLog whereCreatedAt($value)
 * @method static Builder<static>|PermissionAuditLog whereId($value)
 * @method static Builder<static>|PermissionAuditLog whereIpAddress($value)
 * @method static Builder<static>|PermissionAuditLog whereMetadata($value)
 * @method static Builder<static>|PermissionAuditLog wherePerformedBy($value)
 * @method static Builder<static>|PermissionAuditLog wherePerformedByName($value)
 * @method static Builder<static>|PermissionAuditLog wherePermissionName($value)
 * @method static Builder<static>|PermissionAuditLog wherePermissionSlug($value)
 * @method static Builder<static>|PermissionAuditLog whereReason($value)
 * @method static Builder<static>|PermissionAuditLog whereScopeId($value)
 * @method static Builder<static>|PermissionAuditLog whereSource($value)
 * @method static Builder<static>|PermissionAuditLog whereSourceId($value)
 * @method static Builder<static>|PermissionAuditLog whereSourceName($value)
 * @method static Builder<static>|PermissionAuditLog whereUserAgent($value)
 * @method static Builder<static>|PermissionAuditLog whereUserEmail($value)
 * @method static Builder<static>|PermissionAuditLog whereUserId($value)
 * @method static Builder<static>|PermissionAuditLog whereUserName($value)
 * @mixin \Eloquent
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
            'source_id' => 'integer',
            'scope_id' => 'integer',
            'performed_by' => 'integer',

            // JSON columns
            'metadata' => AsArrayObject::class,

            // DateTime columns
            'created_at' => 'datetime',
        ];
    }

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
