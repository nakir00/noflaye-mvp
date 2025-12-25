<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Kitchen extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'is_active',
        'operating_hours',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'operating_hours' => 'array',
            'capacity' => 'integer',
        ];
    }

    // Relations
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kitchen_user')->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_kitchen')->withTimestamps();
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'kitchen_driver')->withTimestamps();
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(Supervisor::class, 'supervisor_kitchen')->withTimestamps();
    }

    public function userGroups(): MorphMany
    {
        return $this->morphMany(UserGroup::class, 'groupable');
    }

    // Filament
    public function getFilamentName(): string
    {
        return $this->name;
    }

    // Helpers
    public function managers(): BelongsToMany
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('slug', 'like', '%manager%');
        });
    }
}
