<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Driver> $drivers
 * @property-read int|null $drivers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kitchen> $kitchens
 * @property-read int|null $kitchens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supervisor> $supervisors
 * @property-read int|null $supervisors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserGroup> $userGroups
 * @property-read int|null $user_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\ShopFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shop whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
            // Boolean columns
            'is_active' => 'boolean',

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // ATTRIBUTES ACCESSORS
    // ========================================

    /**
     * Get the shop name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the shop slug.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtolower(trim($value)) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Get the shop description.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the shop phone.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the shop email.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtolower(trim($value)) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Get the shop address.
     */
    protected function address(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the is_active status.
     */
    protected function isActive(): Attribute
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_user')->withTimestamps();
    }

    public function kitchens(): BelongsToMany
    {
        return $this->belongsToMany(Kitchen::class, 'shop_kitchen')->withTimestamps();
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'shop_driver')->withTimestamps();
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(Supervisor::class, 'supervisor_shop')->withTimestamps();
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
     * Get managers of this shop
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function managers(): BelongsToMany
    {
        return $this->users()->whereHas('templates', function ($query) {
            $query->where('slug', 'like', '%manager%');
        });
    }

    /**
     * Get all staff members of this shop
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staff(): BelongsToMany
    {
        return $this->users()->whereHas('templates', function ($query) {
            $query->where('slug', 'like', '%staff%');
        });
    }

    /**
     * Scope query to only active shops
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
