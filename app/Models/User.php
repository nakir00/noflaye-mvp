<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $primary_template_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionDelegation> $delegationsGiven
 * @property-read int|null $delegations_given_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionDelegation> $delegationsReceived
 * @property-read int|null $delegations_received_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Driver> $drivers
 * @property-read int|null $drivers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kitchen> $kitchens
 * @property-read int|null $kitchens_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionRequest> $permissionRequests
 * @property-read int|null $permission_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\PermissionTemplate|null $primaryTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Scope> $scopes
 * @property-read int|null $scopes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shop> $shops
 * @property-read int|null $shops_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supervisor> $supervisors
 * @property-read int|null $supervisors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supplier> $suppliers
 * @property-read int|null $suppliers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PermissionTemplate> $templates
 * @property-read int|null $templates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserGroup> $userGroups
 * @property-read int|null $user_groups_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrimaryTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, LogsActivityTrait, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'primary_template_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // DateTime columns
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',

            // Integer columns
            'primary_template_id' => 'integer',

            // Password hashing
            'password' => 'hashed',
        ];
    }

    // ═══════════════════════════════════════════════════════
    // ATTRIBUTES ACCESSORS
    // ═══════════════════════════════════════════════════════

    /**
     * Get the user's name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? trim($value) : null,
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    /**
     * Get the user's email.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtolower(trim($value)) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Get the email verified at timestamp.
     */
    protected function emailVerifiedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Get the primary template ID.
     */
    protected function primaryTemplateId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Get the remember token.
     */
    protected function rememberToken(): Attribute
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

    // ═══════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════

    /**
     * Template principal de l'utilisateur
     */
    public function primaryTemplate(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'primary_template_id');
    }

    /**
     * Templates assignés à l'utilisateur
     */
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(PermissionTemplate::class, 'user_templates', 'user_id', 'template_id')
            ->withPivot('scope_id', 'template_version', 'auto_upgrade', 'auto_sync', 'valid_from', 'valid_until')
            ->withTimestamps();
    }

    /**
     * Permissions directes de l'utilisateur
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot(['scope_id', 'expires_at', 'source', 'source_id', 'conditions'])
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
            ->withPivot(['scope_id'])
            ->withTimestamps();
    }

    public function delegationsGiven(): HasMany
    {
        return $this->hasMany(PermissionDelegation::class, 'delegator_id');
    }

    public function delegationsReceived(): HasMany
    {
        return $this->hasMany(PermissionDelegation::class, 'delegatee_id');
    }

    public function permissionRequests(): HasMany
    {
        return $this->hasMany(PermissionRequest::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(Scope::class, 'scopable_id')
            ->where('scopable_type', self::class);
    }

    // ═══════════════════════════════════════════════════════
    // VÉRIFICATION TEMPLATES
    // ═══════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur a un template spécifique
     */
    public function hasTemplate(string $templateSlug): bool
    {
        return $this->primaryTemplate?->slug === $templateSlug
            || $this->templates->contains('slug', $templateSlug);
    }

    /**
     * Vérifie si l'utilisateur a AU MOINS UN des templates
     */
    public function hasAnyTemplate(array $templateSlugs): bool
    {
        if ($this->primaryTemplate && in_array($this->primaryTemplate->slug, $templateSlugs)) {
            return true;
        }

        return $this->templates->whereIn('slug', $templateSlugs)->isNotEmpty();
    }

    /**
     * Vérifie si l'utilisateur a TOUS les templates
     */
    public function hasAllTemplates(array $templateSlugs): bool
    {
        $userTemplateSlugs = $this->getTemplateSlugs();

        foreach ($templateSlugs as $slug) {
            if (! in_array($slug, $userTemplateSlugs)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Récupère tous les slugs des templates
     */
    public function getTemplateSlugs(): array
    {
        $slugs = $this->templates->pluck('slug')->toArray();

        if ($this->primaryTemplate) {
            $slugs[] = $this->primaryTemplate->slug;
        }

        return array_unique($slugs);
    }

    // Compatibility aliases for backward compatibility during migration
    public function hasRole(string $roleSlug): bool
    {
        return $this->hasTemplate($roleSlug);
    }

    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->hasAnyTemplate($roleSlugs);
    }

    public function hasAllRoles(array $roleSlugs): bool
    {
        return $this->hasAllTemplates($roleSlugs);
    }

    public function getRoleSlugs(): array
    {
        return $this->getTemplateSlugs();
    }

    // ═══════════════════════════════════════════════════════
    // VÉRIFICATION PERMISSIONS
    // ═══════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur a une permission
     *
     * @param  string  $permissionSlug  Format: 'resource.action'
     * @param  Scope|int|null  $scope  Scope instance, ID, or null for global
     */
    public function hasPermission(string $permissionSlug, Scope|int|null $scope = null): bool
    {
        // Utiliser le nouveau service PermissionChecker
        return app(\App\Services\Permissions\PermissionChecker::class)
            ->checkWithScope($this, $permissionSlug, $scope);
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
        return $this->shops()->where('shops.id', $shopId)->exists();
    }

    /**
     * Vérifie si l'utilisateur gère un fournisseur
     */
    public function managesSupplier(int $supplierId): bool
    {
        return $this->suppliers()->where('suppliers.id', $supplierId)->exists();
    }

    /**
     * Récupère toutes les boutiques gérées
     */
    public function getManagedShops(): Collection
    {
        if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
            return Shop::all();
        }

        return $this->shops;
    }

    /**
     * Récupère tous les fournisseurs gérés
     */
    public function getManagedSuppliers(): Collection
    {
        if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
            return Supplier::all();
        }

        return $this->suppliers;
    }

    /**
     * Récupère tous les superviseurs gérés
     */
    public function getManagedSupervisors(): Collection
    {
        if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
            return Supervisor::all();
        }

        return $this->supervisors;
    }

    /**
     * Récupère toutes les cuisines gérées
     */
    public function getManagedKitchens(): Collection
    {
        if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
            return Kitchen::all();
        }

        return $this->kitchens;
    }

    /**
     * Récupère tous les chauffeurs gérés
     */
    public function getManagedDrivers(): Collection
    {
        if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
            return Driver::all();
        }

        return $this->drivers;
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
                'entities' => $managedShops->map(fn ($shop) => [
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
                'entities' => $managedKitchens->map(fn ($kitchen) => [
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
                'entities' => $managedDrivers->map(fn ($driver) => [
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
                'entities' => $managedSuppliers->map(fn ($supplier) => [
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
                'entities' => $managedSupervisors->map(fn ($supervisor) => [
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
        if ($this->primary_template_id) {
            $template = $this->primaryTemplate;

            return $this->getPanelUrlForTemplate($template->slug);
        }

        $template = $this->templates()->first();

        if ($template) {
            return $this->getPanelUrlForTemplate($template->slug);
        }

        return '/';
    }

    /**
     * URL du panel selon le template
     */
    protected function getPanelUrlForTemplate(string $templateSlug): string
    {
        return match (true) {
            in_array($templateSlug, ['super_admin', 'admin']) => '/admin',
            str_starts_with($templateSlug, 'shop_manager') => '/shop',
            str_starts_with($templateSlug, 'kitchen') => '/kitchen',
            $templateSlug === 'driver' => '/driver',
            str_starts_with($templateSlug, 'supplier') => '/supplier',
            default => '/',
        };
    }

    // ========================================
    // ACTIVITY LOG CONFIGURATION
    // ========================================

    /**
     * Get the log name for activity logging
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['name', 'email', 'primary_template_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
