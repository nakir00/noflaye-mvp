<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionGroup extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'level',
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

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // ATTRIBUTES ACCESSORS
    // ========================================

    /**
     * Get the group name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the group slug.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtolower(trim($value)) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Get the group description.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the parent ID.
     */
    protected function parentId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Get the level.
     */
    protected function level(): Attribute
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PermissionGroup::class, 'parent_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'permission_group_id');
    }
}
