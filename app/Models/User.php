<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'primary_role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ═══════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════

    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'primary_role_id');
    }

    /**
     * Tous les rôles de l'utilisateur (multi-rôles)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['scope_type', 'scope_id', 'valid_from', 'valid_until', 'granted_by'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('user_roles.valid_until')
                      ->orWhere('user_roles.valid_until', '>', now());
                })
                ->where(function ($q) {
                    $q->whereNull('user_roles.valid_from')
                      ->orWhere('user_roles.valid_from', '<=', now());
                });
            })
            ->withTimestamps();
    }

    /**
     * Permissions directes de l'utilisateur
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot(['permission_type', 'scope_type', 'scope_id', 'valid_from', 'valid_until', 'granted_by', 'reason'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('user_permissions.valid_until')
                      ->orWhere('user_permissions.valid_until', '>', now());
                })
                ->where(function ($q) {
                    $q->whereNull('user_permissions.valid_from')
                      ->orWhere('user_permissions.valid_from', '<=', now());
                });
            })
            ->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_user')->withTimestamps();
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_user')->withTimestamps();
    }

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

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_members')
            ->withPivot(['scope_type', 'scope_id', 'valid_from', 'valid_until', 'assigned_by'])
            ->withTimestamps();
    }

    // ═══════════════════════════════════════════════════════
    // VÉRIFICATION RÔLES
    // ═══════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists() ||
               $this->primaryRole?->slug === $roleSlug;
    }

    /**
     * Vérifie si l'utilisateur a AU MOINS UN des rôles
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists() ||
               ($this->primaryRole && in_array($this->primaryRole->slug, $roleSlugs));
    }

    /**
     * Vérifie si l'utilisateur a TOUS les rôles
     */
    public function hasAllRoles(array $roleSlugs): bool
    {
        $userRoles = $this->getRoleSlugs();
        return count(array_intersect($roleSlugs, $userRoles)) === count($roleSlugs);
    }

    /**
     * Récupère tous les slugs des rôles
     */
    public function getRoleSlugs(): array
    {
        $slugs = $this->roles()->pluck('slug')->toArray();

        if ($this->primaryRole) {
            $slugs[] = $this->primaryRole->slug;
        }

        return array_unique($slugs);
    }

    // ═══════════════════════════════════════════════════════
    // VÉRIFICATION PERMISSIONS
    // ═══════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur a une permission
     *
     * @param string $permissionSlug Format: 'resource.action' ou 'resource.scope.action'
     * @param string|null $scopeType Type de scope ('shop', 'supplier', null)
     * @param int|null $scopeId ID du scope
     */
    public function hasPermission(string $permissionSlug, ?string $scopeType = null, ?int $scopeId = null): bool
    {
        // Utiliser le service PermissionChecker
        return app(\App\Services\PermissionChecker::class)
            ->check($this, $permissionSlug, $scopeType, $scopeId);
    }

    /**
     * Vérifie si a AU MOINS UNE des permissions
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $slug) {
            if ($this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }

    // ═══════════════════════════════════════════════════════
    // FILAMENT - ACCÈS PANELS
    // ═══════════════════════════════════════════════════════

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

    // ═══════════════════════════════════════════════════════
    // GESTION TENANTS
    // ═══════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur gère une boutique
     */
    public function managesShop(int $shopId): bool
    {
        return $this->shops()->where('shops.id', $shopId)->exists() ||
               $this->roles()
                   ->wherePivot('scope_type', 'shop')
                   ->wherePivot('scope_id', $shopId)
                   ->exists();
    }

    /**
     * Vérifie si l'utilisateur gère un fournisseur
     */
    public function managesSupplier(int $supplierId): bool
    {
        return $this->suppliers()->where('suppliers.id', $supplierId)->exists() ||
               $this->roles()
                   ->wherePivot('scope_type', 'supplier')
                   ->wherePivot('scope_id', $supplierId)
                   ->exists();
    }

    /**
     * Récupère toutes les boutiques gérées
     */
    public function getManagedShops(): Collection
    {
        if ($this->hasAnyRole(['super_admin', 'admin'])) {
            return Shop::all();
        }

        // Boutiques directement liées
        $directShops = $this->shops()->pluck('shops.id');

        // Boutiques via user_roles avec scope
        $scopedShops = $this->roles()
            ->wherePivot('scope_type', 'shop')
            ->whereNotNull('user_roles.scope_id')
            ->pluck('user_roles.scope_id');

        $allShopIds = $directShops->merge($scopedShops)->unique();

        return Shop::whereIn('id', $allShopIds)->get();
    }

    /**
     * Récupère tous les fournisseurs gérés
     */
    public function getManagedSuppliers(): Collection
    {
        if ($this->hasAnyRole(['super_admin', 'admin'])) {
            return Supplier::all();
        }

        $directSuppliers = $this->suppliers()->pluck('suppliers.id');

        $scopedSuppliers = $this->roles()
            ->wherePivot('scope_type', 'supplier')
            ->whereNotNull('user_roles.scope_id')
            ->pluck('user_roles.scope_id');

        $allSupplierIds = $directSuppliers->merge($scopedSuppliers)->unique();

        return Supplier::whereIn('id', $allSupplierIds)->get();
    }

    /**
     * Récupère tous les superviseurs gérés
     */
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

    /**
     * Récupère toutes les cuisines gérées
     */
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

    /**
     * Récupère tous les chauffeurs gérés
     */
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

    /**
     * Vérifie si l'utilisateur gère un superviseur
     */
    public function managesSupervisor(int $id): bool
    {
        return $this->getManagedSupervisors()->contains('id', $id);
    }

    /**
     * Vérifie si l'utilisateur gère une cuisine
     */
    public function managesKitchen(int $id): bool
    {
        return $this->getManagedKitchens()->contains('id', $id);
    }

    /**
     * Vérifie si l'utilisateur gère un chauffeur
     */
    public function managesDriver(int $id): bool
    {
        return $this->getManagedDrivers()->contains('id', $id);
    }

    // ═══════════════════════════════════════════════════════
    // PANEL SWITCHER
    // ═══════════════════════════════════════════════════════

    /**
     * Récupère tous les panels accessibles par l'utilisateur
     * Pour le panel switcher dans Filament
     */
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

    /**
     * URL du panel par défaut (après login)
     */
    public function getDefaultPanelUrl(): string
    {
        if ($this->primary_role_id) {
            $role = $this->primaryRole;
            return $this->getPanelUrlForRole($role->slug);
        }

        $role = $this->roles()->orderBy('level', 'desc')->first();

        if ($role) {
            return $this->getPanelUrlForRole($role->slug);
        }

        return '/';
    }

    /**
     * URL du panel selon le rôle
     */
    protected function getPanelUrlForRole(string $roleSlug): string
    {
        return match (true) {
            in_array($roleSlug, ['super_admin', 'admin']) => '/admin',
            str_starts_with($roleSlug, 'shop_manager') => '/shop',
            str_starts_with($roleSlug, 'kitchen') => '/kitchen',
            $roleSlug === 'driver' => '/driver',
            str_starts_with($roleSlug, 'supplier') => '/supplier',
            default => '/',
        };
    }
}
