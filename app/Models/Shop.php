<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Shop extends Model implements HasName
{
    /** @use HasFactory<\Database\Factories\ShopFactory> */
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_user')->withTimestamps();
    }

    public function userGroups(): MorphMany
    {
        return $this->morphMany(UserGroup::class, 'groupable');
    }

    /**
     * Nom du tenant pour Filament
     * Implémentation de HasName
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * Managers de cette boutique
     */
    public function managers(): BelongsToMany
    {
        return $this->users()
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'like', '%manager%');
            });
    }
}
