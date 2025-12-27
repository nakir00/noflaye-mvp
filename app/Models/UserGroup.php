<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class UserGroup extends Model
{
    /** @use HasFactory<\Database\Factories\UserGroupFactory> */
    use HasFactory;

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
