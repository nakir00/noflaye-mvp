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

            'shop' => $this->hasAnyRole([
                'shop_manager',
                'shop_manager_senior',
                'shop_manager_junior',
                'shop_manager_trainee'
            ]) || $this->shops()->exists(),

            'kitchen' => $this->hasAnyRole([
                'kitchen_manager',
                'kitchen_staff'
            ]) || $this->shops()->exists(),

            'driver' => $this->hasRole('driver'),

            'supplier' => $this->hasAnyRole([
                'supplier_manager',
                'supplier_staff'
            ]) || $this->suppliers()->exists(),

            default => false,
        };
    }

    public function getTenants(Panel $panel): Collection
    {
        $panelId = $panel->getId();

        // Super Admin et Admin voient tout
        if ($this->hasAnyRole(['super_admin', 'admin'])) {
            return match ($panelId) {
                'shop', 'kitchen' => Shop::all(),
                'supplier' => Supplier::all(),
                default => collect(),
            };
        }

        // Autres utilisateurs voient leurs tenants
        return match ($panelId) {
            'shop', 'kitchen' => $this->shops,
            'supplier' => $this->suppliers,
            default => collect(),
        };
    }

    public function canAccessTenant(Model $tenant): bool
    {
        // Super Admin et Admin peuvent tout voir
        if ($this->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Vérifier si l'utilisateur gère ce tenant
        if ($tenant instanceof Shop) {
            return $this->managesShop($tenant->id);
        }

        if ($tenant instanceof Supplier) {
            return $this->managesSupplier($tenant->id);
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
        $roleSlugs = $this->getRoleSlugs();

        // Admin Panel
        if (array_intersect(['super_admin', 'admin'], $roleSlugs)) {
            $panels[] = [
                'id' => 'admin',
                'name' => 'Administration',
                'url' => '/admin',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'danger',
            ];
        }

        // Shop Panel
        if (array_intersect(['shop_manager', 'shop_manager_senior', 'shop_manager_junior', 'shop_manager_trainee'], $roleSlugs) || $this->shops()->exists()) {
            $panels[] = [
                'id' => 'shop',
                'name' => 'Gestion Boutique',
                'url' => '/shop',
                'icon' => 'heroicon-o-building-storefront',
                'color' => 'primary',
            ];
        }

        // Kitchen Panel
        if (array_intersect(['kitchen_manager', 'kitchen_staff'], $roleSlugs) || $this->shops()->exists()) {
            $panels[] = [
                'id' => 'kitchen',
                'name' => 'Cuisine',
                'url' => '/kitchen',
                'icon' => 'heroicon-o-fire',
                'color' => 'warning',
            ];
        }

        // Driver Panel
        if (in_array('driver', $roleSlugs)) {
            $panels[] = [
                'id' => 'driver',
                'name' => 'Livraison',
                'url' => '/driver',
                'icon' => 'heroicon-o-truck',
                'color' => 'success',
            ];
        }

        // Supplier Panel
        if (array_intersect(['supplier_manager', 'supplier_staff'], $roleSlugs) || $this->suppliers()->exists()) {
            $panels[] = [
                'id' => 'supplier',
                'name' => 'Fournisseur',
                'url' => '/supplier',
                'icon' => 'heroicon-o-cube',
                'color' => 'info',
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
