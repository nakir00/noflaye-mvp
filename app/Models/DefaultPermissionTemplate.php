<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DefaultPermissionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'scope_type',
        'scope_id',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Relations
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'template_roles')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'template_permissions')->withTimestamps();
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'template_user_groups')->withTimestamps();
    }

    // Apply template to user
    public function applyToUser(User $user, ?string $scopeType = null, ?int $scopeId = null): void
    {
        $effectiveScopeType = $scopeType ?? $this->scope_type;
        $effectiveScopeId = $scopeId ?? $this->scope_id;

        // Attach roles
        foreach ($this->roles as $role) {
            $user->roles()->attach($role->id, [
                'scope_type' => $effectiveScopeType,
                'scope_id' => $effectiveScopeId,
                'valid_from' => now(),
                'granted_by' => auth()->id(),
                'reason' => "Applied from template: {$this->name}",
            ]);
        }

        // Attach permissions
        foreach ($this->permissions as $permission) {
            $user->permissions()->attach($permission->id, [
                'permission_type' => 'grant',
                'scope_type' => $effectiveScopeType,
                'scope_id' => $effectiveScopeId,
                'valid_from' => now(),
                'granted_by' => auth()->id(),
                'reason' => "Applied from template: {$this->name}",
            ]);
        }

        // Attach user groups
        foreach ($this->userGroups as $group) {
            $user->userGroups()->attach($group->id, [
                'scope_type' => $effectiveScopeType,
                'scope_id' => $effectiveScopeId,
                'valid_from' => now(),
            ]);
        }
    }
}
