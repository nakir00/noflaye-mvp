🔧 MODÈLES ELOQUENT

Modèle : Supervisorphp<?php

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
}Modèle : Kitchenphp<?php

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
}Modèle : Driverphp<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
            'is_active' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    // Relations
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

    // Filament
    public function getFilamentName(): string
    {
        return $this->name;
    }
}Modèle : DefaultPermissionTemplatephp<?php

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
}Modèle : PanelConfigurationphp<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'panel_id',
        'can_manage_users',
        'can_manage_roles',
        'can_manage_permissions',
        'can_invite_users',
        'can_assign_managers',
        'can_create_templates',
        'can_assign_templates',
        'can_view_own_permissions',
        'additional_settings',
    ];

    protected function casts(): array
    {
        return [
            'can_manage_users' => 'boolean',
            'can_manage_roles' => 'boolean',
            'can_manage_permissions' => 'boolean',
            'can_invite_users' => 'boolean',
            'can_assign_managers' => 'boolean',
            'can_create_templates' => 'boolean',
            'can_assign_templates' => 'boolean',
            'can_view_own_permissions' => 'boolean',
            'additional_settings' => 'array',
        ];
    }

    public static function getForPanel(string $panelId): ?self
    {
        return static::where('panel_id', $panelId)->first();
    }

    public function canPerform(string $capability): bool
    {
        return $this->getAttribute($capability) ?? false;
    }
}Extension du Modèle User (ajouts)php<?php

namespace App\Models;

// Dans le modèle User existant, ajouter ces relations et méthodes :

// Relations vers nouvelles entités
public function supervisors(): BelongsToMany
{
    return $this->belongsToMany(Supervisor::class, 'supervisor_user')->withTimestamps();
}

public function kitchens(): BelongsToMany
{
    return $this->belongsToMany(Kitchen::class, 'kitchen_user')->withTimestamps();
}

public function drivers(): BelongsToMany
{
    return $this->belongsToMany(Driver::class, 'driver_user')->withTimestamps();
}

// Méthodes getManaged pour chaque entité
public function getManagedSupervisors(): Collection
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return Supervisor::all();
    }

    $direct = $this->supervisors()->pluck('supervisors.id');
    $scoped = $this->roles()
        ->wherePivot('scope_type', 'supervisor')
        ->pluck('user_roles.scope_id')
        ->filter();

    return Supervisor::whereIn('id', $direct->merge($scoped)->unique())->get();
}

public function getManagedKitchens(): Collection
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return Kitchen::all();
    }

    $direct = $this->kitchens()->pluck('kitchens.id');
    $scoped = $this->roles()
        ->wherePivot('scope_type', 'kitchen')
        ->pluck('user_roles.scope_id')
        ->filter();

    return Kitchen::whereIn('id', $direct->merge($scoped)->unique())->get();
}

public function getManagedDrivers(): Collection
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return Driver::all();
    }

    $direct = $this->drivers()->pluck('drivers.id');
    $scoped = $this->roles()
        ->wherePivot('scope_type', 'driver')
        ->pluck('user_roles.scope_id')
        ->filter();

    return Driver::whereIn('id', $direct->merge($scoped)->unique())->get();
}

// Méthodes manages
public function managesSupervisor(int $id): bool
{
    return $this->getManagedSupervisors()->contains('id', $id);
}

public function managesKitchen(int $id): bool
{
    return $this->getManagedKitchens()->contains('id', $id);
}

public function managesDriver(int $id): bool
{
    return $this->getManagedDrivers()->contains('id', $id);
}

// Mise à jour canAccessPanel
public function canAccessPanel(Panel $panel): bool
{
    $panelId = $panel->getId();

    return match ($panelId) {
        'admin' => $this->hasAnyRole(['super_admin', 'admin']),
        'shop' => $this->hasAnyRole(['shop_manager', 'shop_staff']) || $this->shops()->exists(),
        'kitchen' => $this->hasAnyRole(['kitchen_manager', 'kitchen_staff']) || $this->kitchens()->exists(),
        'driver' => $this->hasRole('driver') || $this->drivers()->exists(),
        'supplier' => $this->hasAnyRole(['supplier_manager', 'supplier_staff']) || $this->suppliers()->exists(),
        'supervisor' => $this->hasAnyRole(['supervisor_manager', 'supervisor_staff']) || $this->supervisors()->exists(),
        default => false,
    };
}

// Mise à jour getTenants
public function getTenants(Panel $panel): Collection
{
    $panelId = $panel->getId();

    return match ($panelId) {
        'admin' => collect([]),
        'shop' => $this->hasAnyRole(['super_admin', 'admin']) ? Shop::all() : $this->shops,
        'kitchen' => $this->hasAnyRole(['super_admin', 'admin']) ? Kitchen::all() : $this->kitchens,
        'driver' => $this->hasAnyRole(['super_admin', 'admin']) ? Driver::all() : $this->drivers,
        'supplier' => $this->hasAnyRole(['super_admin', 'admin']) ? Supplier::all() : $this->suppliers,
        'supervisor' => $this->hasAnyRole(['super_admin', 'admin']) ? Supervisor::all() : $this->supervisors,
        default => collect([]),
    };
}

// Mise à jour canAccessTenant
public function canAccessTenant(Model $tenant): bool
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return true;
    }

    if ($tenant instanceof Shop) {
        return $this->managesShop($tenant->id);
    }

    if ($tenant instanceof Kitchen) {
        return $this->managesKitchen($tenant->id);
    }

    if ($tenant instanceof Driver) {
        return $this->managesDriver($tenant->id);
    }

    if ($tenant instanceof Supplier) {
        return $this->managesSupplier($tenant->id);
    }

    if ($tenant instanceof Supervisor) {
        return $this->managesSupervisor($tenant->id);
    }

    return false;
}

// Nouvelle méthode getAccessiblePanels avec entités
public function getAccessiblePanels(): array
{
    $panels = [];

    // Admin Panel
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        $panels[] = [
            'id' => 'admin',
            'name' => 'Administration',
            'url' => '/admin',
            'icon' => 'heroicon-o-shield-check',
            'color' => 'danger',
            'entities' => [],
        ];
    }

    // Shop Panel
    $managedShops = $this->getManagedShops();
    if ($managedShops->isNotEmpty() || $this->hasAnyRole(['shop_manager', 'shop_staff'])) {
        $panels[] = [
            'id' => 'shop',
            'name' => 'Boutiques',
            'url' => '/shop',
            'icon' => 'heroicon-o-building-storefront',
            'color' => 'primary',
            'entities' => $managedShops->map(fn($shop) => [
                'id' => $shop->id,
                'name' => $shop->name,
                'url' => "/shop/{$shop->slug}",
                'linked_kitchens' => $shop->kitchens->pluck('name')->toArray(),
                'linked_drivers' => $shop->drivers->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    // Kitchen Panel
    $managedKitchens = $this->getManagedKitchens();
    if ($managedKitchens->isNotEmpty() || $this->hasAnyRole(['kitchen_manager', 'kitchen_staff'])) {
        $panels[] = [
            'id' => 'kitchen',
            'name' => 'Cuisines',
            'url' => '/kitchen',
            'icon' => 'heroicon-o-fire',
            'color' => 'warning',
            'entities' => $managedKitchens->map(fn($kitchen) => [
                'id' => $kitchen->id,
                'name' => $kitchen->name,
                'url' => "/kitchen/{$kitchen->slug}",
                'linked_shops' => $kitchen->shops->pluck('name')->toArray(),
                'linked_drivers' => $kitchen->drivers->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    // Driver Panel
    $managedDrivers = $this->getManagedDrivers();
    if ($managedDrivers->isNotEmpty() || $this->hasRole('driver')) {
        $panels[] = [
            'id' => 'driver',
            'name' => 'Livraisons',
            'url' => '/driver',
            'icon' => 'heroicon-o-truck',
            'color' => 'success',
            'entities' => $managedDrivers->map(fn($driver) => [
                'id' => $driver->id,
                'name' => $driver->name,
                'url' => "/driver/{$driver->slug}",
                'linked_shops' => $driver->shops->pluck('name')->toArray(),
                'linked_kitchens' => $driver->kitchens->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    // Supplier Panel
    $managedSuppliers = $this->getManagedSuppliers();
    if ($managedSuppliers->isNotEmpty() || $this->hasAnyRole(['supplier_manager', 'supplier_staff'])) {
        $panels[] = [
            'id' => 'supplier',
            'name' => 'Fournisseurs',
            'url' => '/supplier',
            'icon' => 'heroicon-o-cube',
            'color' => 'info',
            'entities' => $managedSuppliers->map(fn($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'url' => "/supplier/{$supplier->slug}",
            ])->toArray(),
        ];
    }

    // Supervisor Panel
    $managedSupervisors = $this->getManagedSupervisors();
    if ($managedSupervisors->isNotEmpty() || $this->hasAnyRole(['supervisor_manager', 'supervisor_staff'])) {
        $panels[] = [
            'id' => 'supervisor',
            'name' => 'Supervision',
            'url' => '/supervisor',
            'icon' => 'heroicon-o-eye',
            'color' => 'purple',
            'entities' => $managedSupervisors->map(fn($supervisor) => [
                'id' => $supervisor->id,
                'name' => $supervisor->name,
                'url' => "/supervisor/{$supervisor->slug}",
                'linked_shops' => $supervisor->shops->pluck('name')->toArray(),
                'linked_kitchens' => $supervisor->kitchens->pluck('name')->toArray(),
                'linked_drivers' => $supervisor->drivers->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    return $panels;
}
