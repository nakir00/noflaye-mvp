<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'active',
        'is_system',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'primary_role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Vérifie si le rôle a une permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Hiérarchie - Rôles parents
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_hierarchy', 'child_role_id', 'parent_role_id')
            ->withPivot('inheritance_type')
            ->withTimestamps();
    }

    /**
     * Hiérarchie - Rôles enfants
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_hierarchy', 'parent_role_id', 'child_role_id')
            ->withPivot('inheritance_type')
            ->withTimestamps();
    }
}
