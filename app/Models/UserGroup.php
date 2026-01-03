<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $groupable_type
 * @property int $groupable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $parent_id
 * @property int $level
 * @property int|null $template_id
 * @property bool $auto_sync_template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserGroup> $children
 * @property-read int|null $children_count
 * @property-read UserGroup|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\PermissionTemplate|null $template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\UserGroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereAutoSyncTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereGroupableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereGroupableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserGroup extends Model
{
    /** @use HasFactory<\Database\Factories\UserGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'level',
        'template_id',
        'auto_sync_template',
        'name',
        'slug',
        'description',
        'groupable_type',
        'groupable_id',
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
            'parent_id' => 'integer',
            'level' => 'integer',
            'template_id' => 'integer',
            'groupable_id' => 'integer',

            // Boolean columns
            'auto_sync_template' => 'boolean',

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(UserGroup::class, 'parent_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'template_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_members')
            ->withPivot('scope_id')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_group_permissions')
            ->withPivot('permission_type')
            ->withTimestamps();
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Get all permissions from template + parent
     */
    public function getAllPermissions(): Collection
    {
        $permissions = collect();

        // Permissions from template
        if ($this->template_id && $this->template) {
            $permissions = $permissions->merge($this->template->getAllPermissions());
        }

        // Permissions from parent
        if ($this->parent_id && $this->parent) {
            $permissions = $permissions->merge($this->parent->getAllPermissions());
        }

        return $permissions->unique('id');
    }
}
