<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogsActivity;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

/**
 * @property int $id
 * @property int|null $permission_group_id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $group_name
 * @property string|null $action_type
 * @property bool $active
 * @property bool $is_system
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PermissionGroup|null $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionTemplate> $templates
 * @property-read int|null $templates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserGroup> $userGroups
 * @property-read int|null $user_groups_count
 * @method static \Database\Factories\PermissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereActionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission wherePermissionGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;
    use LogsActivityTrait;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permission_group_id',
        'group_name',
        'action_type',
        'active',
        'is_system',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Boolean columns
            'active' => 'boolean',
            'is_system' => 'boolean',

            // Integer columns
            'permission_group_id' => 'integer',

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // ATTRIBUTES ACCESSORS
    // ========================================

    /**
     * Get the permission name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the permission slug.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtolower(trim($value)) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Get the permission description.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the permission group ID.
     */
    protected function permissionGroupId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Get the group name.
     */
    protected function groupName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the action type.
     */
    protected function actionType(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the active status.
     */
    protected function active(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Get the is_system status.
     */
    protected function isSystem(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Get the created at timestamp.
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Get the updated at timestamp.
     */
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(PermissionTemplate::class, 'template_permissions', 'permission_id', 'template_id')
            ->withPivot('source', 'wildcard_id', 'sort_order')
            ->withTimestamps();
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_permissions')
            ->withPivot('permission_type')
            ->withTimestamps();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope query to only active permissions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope query by permission group
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $groupId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGroup($query, ?int $groupId)
    {
        if ($groupId === null) {
            return $query;
        }

        return $query->where('permission_group_id', $groupId);
    }

    /**
     * Scope query to system permissions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope query to search permissions by name, slug, or description
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // ========================================
    // ACTIVITY LOG CONFIGURATION
    // ========================================

    /**
     * Get the log name for activity logging
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'active', 'is_system', 'permission_group_id', 'action_type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
