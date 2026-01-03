<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Permission Model
 * 
 * Represents the pivot table between users and permissions
 * as a full Eloquent model to enable queries in widgets/stats
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $scope_id
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>|null $conditions
 * @property string $source
 * @property int|null $source_id
 * @property-read \App\Models\Permission $permission
 * @property-read \App\Models\Scope|null $scope
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission fromSource(string $source)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereScopeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereUserId($value)
 * @mixin \Eloquent
 */
class UserPermission extends Model
{
    /**
     * The table associated with the model
     */
    protected $table = 'user_permissions';

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'user_id',
        'permission_id',
        'scope_id',
        'expires_at',
        'source',
        'source_id',
        'conditions',
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
            'source_id' => 'integer',

            // JSON columns
            'conditions' => AsArrayObject::class,

            // DateTime columns
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the permission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the scope (if scoped)
     */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    /**
     * Check if permission is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if permission is active (not expired)
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope query to active permissions only
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope query to expired permissions
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    /**
     * Scope query to permissions from specific source
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
