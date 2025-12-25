<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Supervisor extends Model implements HasName
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
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'supervisor_user')->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'supervisor_shop')->withTimestamps();
    }

    public function kitchens(): BelongsToMany
    {
        return $this->belongsToMany(Kitchen::class, 'supervisor_kitchen')->withTimestamps();
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'supervisor_driver')->withTimestamps();
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
