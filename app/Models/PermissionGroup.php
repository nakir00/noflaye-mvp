<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionGroup extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionGroupFactory> */
    use HasFactory;

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PermissionGroup::class, 'parent_id');
    }
}
