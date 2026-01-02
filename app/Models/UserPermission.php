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
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property int|null $scope_id
 * @property \Carbon\Carbon|null $expires_at
 * @property string|null $source
 * @property int|null $source_id
 * @property array|null $conditions
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @author Noflaye Box Team
 * @version 1.0.0
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
