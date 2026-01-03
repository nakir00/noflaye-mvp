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
 * @property string|null $vehicle_type
 * @property string|null $vehicle_number
 * @property string|null $license_number
 * @property bool $is_active
 * @property bool $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kitchen> $kitchens
 * @property-read int|null $kitchens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shop> $shops
 * @property-read int|null $shops_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supervisor> $supervisors
 * @property-read int|null $supervisors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserGroup> $userGroups
 * @property-read int|null $user_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereVehicleNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereVehicleType($value)
 * @mixin \Eloquent
 */
class Driver extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'is_active',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            // Boolean columns
            'is_active' => 'boolean',
            'is_available' => 'boolean',

            // DateTime columns
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // ATTRIBUTES ACCESSORS
    // ========================================

    /**
     * Get the driver name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the driver slug.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtolower(trim($value)) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Get the driver description.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the driver phone.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the driver email.
     */
    protected function email(): Attribute | string | null
    {
        return Attribute::make(
            get: fn (?string $value): string | null => $value ? strtolower(trim($value)) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Get the vehicle type.
     */
    protected function vehicleType(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the vehicle number.
     */
    protected function vehicleNumber(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtoupper(trim($value)) : null,
            set: fn (?string $value) => $value ? strtoupper(trim($value)) : null,
        );
    }

    /**
     * Get the license number.
     */
    protected function licenseNumber(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtoupper(trim($value)) : null,
            set: fn (?string $value) => $value ? strtoupper(trim($value)) : null,
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
     * Get the is_available status.
     */
    protected function isAvailable(): Attribute
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
        return $this->belongsToMany(User::class, 'driver_user')->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_driver')->withTimestamps();
    }

    public function kitchens(): BelongsToMany
    {
        return $this->belongsToMany(Kitchen::class, 'kitchen_driver')->withTimestamps();
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(Supervisor::class, 'supervisor_driver')->withTimestamps();
    }

    public function userGroups(): MorphMany
    {
        return $this->morphMany(UserGroup::class, 'groupable');
    }

    // ========================================
    // FILAMENT
    // ========================================

    public function getFilamentName(): string
    {
        return $this->name;
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope query to only active drivers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only available drivers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                     ->where('is_active', true);
    }
}
