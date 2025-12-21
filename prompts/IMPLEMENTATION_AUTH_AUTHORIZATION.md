# Implémentation Services d'Authentification & Autorisation - Noflaye Box

## 🎯 Contexte

Le projet a été initialisé avec succès avec :
- ✅ Laravel 12 + Filament v4
- ✅ 5 Panels Filament (Admin, Shop, Kitchen, Driver, Supplier)
- ✅ Inertia v2 + React + TypeScript
- ✅ Migrations de base (users, roles, permissions, shops, suppliers)
- ✅ Seeders avec données de test

**Prochaine étape** : Implémenter le système d'authentification et d'autorisation complet selon l'architecture définie.

---

## 📋 Ce qui manque actuellement

### **1. Modèles Incomplets**

Les modèles actuels ne reflètent pas complètement l'architecture RBAC+GBAC+Context Rules que nous avions définie. Voici ce qui doit être ajouté/corrigé :

#### **User.php**
Manque :
- Méthode `hasPermission()` pour vérifier permissions
- Méthode `getAccessiblePanels()` pour panel switcher
- Méthode `managesShop()`, `managesSupplier()`
- Relations complètes vers permissions directes
- Support multi-rôles avec scopes

#### **Role.php**
Manque :
- Relations complètes
- Méthode `hasPermission()`
- Support hiérarchie (parent/child)

#### **Permission.php**
Manque :
- `$fillable` et casts
- Relations vers groups
- Méthodes helper

#### **Shop.php & Supplier.php**
Manque :
- Implémentation complète de `FilamentTenant`
- Méthode `getTenantName()`
- Relations vers managers

### **2. Migrations Incomplètes**

Les migrations actuelles sont simplifiées. Il manque plusieurs tables essentielles :

#### **Tables manquantes** :
- `user_permissions` (permissions directes - grant/revoke)
- `departments` (organisation)
- `context_rules` (règles dynamiques)
- `permission_conditions` (conditions statiques)
- `permission_constraints` (contraintes temps/montant/quota)
- `approval_requests` (workflows d'approbation)
- `approval_workflow_steps` (multi-niveaux)
- `field_permissions` (field-level security)
- `temporary_permissions` (permissions temporaires/urgence)
- `activity_logs` (audit trail)

#### **Migrations à modifier** :
- `user_roles` : Ajouter colonnes `scope_type`, `scope_id`, `valid_from`, `valid_until`, `granted_by`
- `permissions` : Ajouter colonnes `group_name`, `action_type`, `active`, `is_system`
- `roles` : Ajouter colonnes `active`, `color`
- `user_groups` : Ajouter colonnes `base_role_id`, `group_type`, `level`, `color`, `active`

### **3. Services Manquants**

Aucun service n'a été créé. Il faut implémenter :
- `PermissionChecker` (vérification permissions)
- `ContextRuleEvaluator` (évaluation règles dynamiques)
- `ApprovalWorkflowService` (gestion workflows)
- `ActivityLogger` (logs d'activité)

---

## 🚀 Plan d'Implémentation

### **Phase 1 : Correction & Complétion des Modèles** ⭐ (À faire en premier)

#### **Tâche 1.1 : Compléter le modèle User**

Fichier : `app/Models/User.php`

```php
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
            ->wherePivot(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('valid_until')
                      ->orWhere('valid_until', '>', now());
                })
                ->where(function ($q) {
                    $q->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
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
            ->wherePivot(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('valid_until')
                      ->orWhere('valid_until', '>', now());
                })
                ->where(function ($q) {
                    $q->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
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
```

#### **Tâche 1.2 : Compléter les autres modèles**

**Role.php** :
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'active',
        'is_system',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'primary_role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Vérifie si le rôle a une permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Hiérarchie - Rôles parents
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_hierarchy', 'child_role_id', 'parent_role_id')
            ->withPivot('inheritance_type')
            ->withTimestamps();
    }

    /**
     * Hiérarchie - Rôles enfants
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_hierarchy', 'parent_role_id', 'child_role_id')
            ->withPivot('inheritance_type')
            ->withTimestamps();
    }
}
```

**Permission.php** :
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permission_group_id',
        'group_name',
        'action_type',
        'active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_permissions')
            ->withPivot('permission_type')
            ->withTimestamps();
    }
}
```

**Shop.php & Supplier.php** :
```php
// Ajouter ces méthodes dans les deux modèles

/**
 * Nom du tenant pour Filament
 */
public function getTenantName(): string
{
    return $this->name;
}

/**
 * Managers de cette entité
 */
public function managers(): BelongsToMany
{
    return $this->users()
        ->whereHas('roles', function ($query) {
            $query->where('slug', 'like', '%manager%');
        });
}
```

---

### **Phase 2 : Migrations Manquantes** ⭐ (À faire après Phase 1)

#### **Tâche 2.1 : Modifier migrations existantes**

**Modifier `user_roles` migration** :
```bash
php artisan make:migration add_scope_and_validity_to_user_roles_table --table=user_roles
```

```php
public function up(): void
{
    Schema::table('user_roles', function (Blueprint $table) {
        // Scope (multi-tenancy)
        $table->string('scope_type', 50)->nullable()->after('role_id')
            ->comment('Type: shop, supplier, region, null=global');
        $table->unsignedBigInteger('scope_id')->nullable()->after('scope_type')
            ->comment('ID du scope (shop_id, supplier_id, etc.)');
        
        // Validité temporelle
        $table->timestamp('valid_from')->default(now())->after('scope_id');
        $table->timestamp('valid_until')->nullable()->after('valid_from')
            ->comment('NULL = permanent');
        
        // Métadonnées
        $table->unsignedBigInteger('granted_by')->nullable()->after('valid_until')
            ->comment('User qui a attribué ce rôle');
        $table->text('reason')->nullable()->after('granted_by');
        
        // Index
        $table->index(['scope_type', 'scope_id']);
        $table->index(['valid_from', 'valid_until']);
        
        // Foreign key
        $table->foreign('granted_by')->references('id')->on('users')->nullOnDelete();
        
        // Modifier la contrainte unique
        $table->dropUnique(['user_id', 'role_id']);
        $table->unique(['user_id', 'role_id', 'scope_type', 'scope_id'], 'user_role_scope_unique');
    });
}
```

#### **Tâche 2.2 : Créer nouvelles migrations essentielles**

**1. user_permissions (permissions directes)** :
```bash
php artisan make:migration create_user_permissions_table
```

**2. context_rules (règles dynamiques)** :
```bash
php artisan make:migration create_context_rules_table
```

**3. approval_requests (workflows)** :
```bash
php artisan make:migration create_approval_requests_table
```

**4. activity_logs (audit trail)** :
```bash
php artisan make:migration create_activity_logs_table
```

Utilise les schémas détaillés du fichier `database-schema-roles-permissions-noflaye.md` dans `/mnt/user-data/outputs/` pour le contenu de chaque migration.

---

### **Phase 3 : Services d'Autorisation** ⭐ (Core du système)

#### **Tâche 3.1 : Service PermissionChecker**

Fichier : `app/Services/PermissionChecker.php`

```bash
php artisan make:class Services/PermissionChecker
```

Ce service doit implémenter la logique de vérification des permissions avec :
1. Vérification permissions via rôles
2. Vérification permissions directes (grant/revoke)
3. Vérification via groupes utilisateurs
4. Évaluation context rules
5. Gestion priorités (user > group > role)

**Structure** :
```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;

class PermissionChecker
{
    public function __construct(
        protected ContextRuleEvaluator $contextRuleEvaluator
    ) {}

    /**
     * Vérifie si un utilisateur a une permission
     */
    public function check(
        User $user,
        string $permissionSlug,
        ?string $scopeType = null,
        ?int $scopeId = null,
        array $context = []
    ): bool {
        // 1. Récupérer la permission
        $permission = Permission::where('slug', $permissionSlug)->first();
        
        if (!$permission || !$permission->active) {
            return false;
        }
        
        // 2. Vérifier permissions directes (PRIORITÉ 1)
        $directPermission = $this->checkDirectPermission($user, $permission, $scopeType, $scopeId);
        
        if ($directPermission !== null) {
            return $directPermission;
        }
        
        // 3. Vérifier permissions via groupes (PRIORITÉ 2)
        $groupPermission = $this->checkGroupPermission($user, $permission, $scopeType, $scopeId);
        
        if ($groupPermission !== null) {
            return $groupPermission;
        }
        
        // 4. Vérifier permissions via rôles (PRIORITÉ 3)
        $rolePermission = $this->checkRolePermission($user, $permission, $scopeType, $scopeId);
        
        if (!$rolePermission) {
            return false;
        }
        
        // 5. Évaluer context rules
        return $this->contextRuleEvaluator->evaluate($user, $permission, $context);
    }
    
    protected function checkDirectPermission(User $user, Permission $permission, ?string $scopeType, ?int $scopeId): ?bool
    {
        // Implémentation vérification permissions directes
        // ...
    }
    
    protected function checkGroupPermission(User $user, Permission $permission, ?string $scopeType, ?int $scopeId): ?bool
    {
        // Implémentation vérification via groupes
        // ...
    }
    
    protected function checkRolePermission(User $user, Permission $permission, ?string $scopeType, ?int $scopeId): bool
    {
        // Implémentation vérification via rôles
        // ...
    }
}
```

#### **Tâche 3.2 : Service ContextRuleEvaluator**

Fichier : `app/Services/ContextRuleEvaluator.php`

```bash
php artisan make:class Services/ContextRuleEvaluator
```

Ce service évalue les règles contextuelles dynamiques avec Symfony ExpressionLanguage.

**Dépendances à installer** :
```bash
composer require symfony/expression-language
```

#### **Tâche 3.3 : Service ActivityLogger**

Fichier : `app/Services/ActivityLogger.php`

```bash
php artisan make:class Services/ActivityLogger
```

Service pour logger toutes les actions dans le système (audit trail).

---

### **Phase 4 : Seeders de Base** ⭐

#### **Tâche 4.1 : RoleSeeder Complet**

Fichier : `database/seeders/RoleSeeder.php`

Créer TOUS les rôles définis dans l'architecture :
- super_admin (level 100)
- admin (level 90)
- shop_manager_senior (level 83)
- shop_manager_junior (level 81)
- shop_manager_trainee (level 80)
- kitchen_manager (level 72)
- kitchen_staff (level 70)
- driver (level 60)
- supplier_manager (level 55)
- supplier_staff (level 53)
- support_manager (level 53)
- support_tier_2 (level 52)
- support_tier_1 (level 51)
- partner (level 50)
- vip_customer (level 10)
- customer (level 1)

#### **Tâche 4.2 : PermissionSeeder Complet**

Fichier : `database/seeders/PermissionSeeder.php`

Créer les groupes de permissions et toutes les permissions essentielles :
- orders.* (read, create, update, cancel, refund, delete)
- products.* (read, create, update, delete, pricing.update)
- inventory.* (read, update, restock, transfer)
- kitchen.* (orders.read, orders.prepare, inventory.manage)
- deliveries.* (read, assign, update)
- analytics.* (shop.read, all.read, reports.export)
- users.* (read, create, update, delete)
- settings.* (manage, roles.manage, permissions.manage)

#### **Tâche 4.3 : RolePermissionSeeder**

Fichier : `database/seeders/RolePermissionSeeder.php`

Assigner les permissions aux rôles selon la matrice définie dans `systeme-autorisations-roles-noflaye.md`.

---

### **Phase 5 : Policies Laravel** ⭐

#### **Tâche 5.1 : Créer les policies de base**

```bash
php artisan make:policy OrderPolicy --model=Order
php artisan make:policy ProductPolicy --model=Product
php artisan make:policy ShopPolicy --model=Shop
php artisan make:policy SupplierPolicy --model=Supplier
php artisan make:policy UserPolicy --model=User
```

**Structure type d'une Policy** :
```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Filament\Facades\Filament;

class OrderPolicy
{
    /**
     * Voir la liste des commandes
     */
    public function viewAny(User $user): bool
    {
        $currentPanel = Filament::getCurrentPanel();
        
        return match($currentPanel?->getId()) {
            'admin' => $user->hasAnyRole(['super_admin', 'admin']) 
                && $user->hasPermission('orders.all.read'),
            
            'shop' => $user->hasAnyRole(['shop_manager', 'shop_manager_senior', 'shop_manager_junior'])
                && $user->hasPermission('orders.read'),
            
            'kitchen' => $user->hasAnyRole(['kitchen_manager', 'kitchen_staff'])
                && $user->hasPermission('kitchen.orders.read'),
            
            'driver' => $user->hasRole('driver')
                && $user->hasPermission('deliveries.read'),
            
            default => false,
        };
    }
    
    /**
     * Voir une commande spécifique
     */
    public function view(User $user, Order $order): bool
    {
        // Super Admin et Admin voient tout
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $user->hasPermission('orders.all.read');
        }
        
        $tenant = Filament::getTenant();
        
        // Shop Manager vérifie si la commande appartient à sa boutique
        if ($tenant && $tenant instanceof \App\Models\Shop) {
            return $order->assigned_shop_id === $tenant->id 
                && $user->hasPermission('orders.read');
        }
        
        return false;
    }
    
    /**
     * Modifier une commande
     */
    public function update(User $user, Order $order): bool
    {
        $currentPanel = Filament::getCurrentPanel();
        $tenant = Filament::getTenant();
        
        return match($currentPanel?->getId()) {
            'admin' => $user->hasAnyRole(['super_admin', 'admin'])
                && $user->hasPermission('orders.update'),
            
            'shop' => $user->hasPermission('orders.update')
                && $tenant
                && $order->assigned_shop_id === $tenant->id
                && $user->managesShop($tenant->id),
            
            'kitchen' => $user->hasPermission('kitchen.orders.prepare')
                && in_array($order->status, ['confirmed', 'preparing']),
            
            default => false,
        };
    }
    
    // Autres méthodes: create, delete, restore, forceDelete...
}
```

#### **Tâche 5.2 : Enregistrer les policies**

Fichier : `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(\App\Models\Order::class, \App\Policies\OrderPolicy::class);
    Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
    Gate::policy(\App\Models\Shop::class, \App\Policies\ShopPolicy::class);
    Gate::policy(\App\Models\Supplier::class, \App\Policies\SupplierPolicy::class);
    Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
}
```

---

### **Phase 6 : Tests** ⭐

#### **Tâche 6.1 : Tests d'autorisation**

```bash
php artisan make:test PermissionCheckerTest --unit
php artisan make:test RolePermissionTest --unit
php artisan make:test MultiRoleSwitchingTest
php artisan make:test MultiTenancyTest
```

**Exemples de tests** :
```php
// tests/Unit/PermissionCheckerTest.php

it('super admin has all permissions', function () {
    $superAdmin = User::factory()->create();
    $role = Role::factory()->create(['slug' => 'super_admin', 'level' => 100]);
    $superAdmin->update(['primary_role_id' => $role->id]);
    
    expect($superAdmin->hasPermission('orders.read'))->toBeTrue();
    expect($superAdmin->hasPermission('products.delete'))->toBeTrue();
});

it('shop manager can only access their shop orders', function () {
    $shop = Shop::factory()->create();
    $manager = User::factory()->create();
    $role = Role::factory()->create(['slug' => 'shop_manager']);
    
    $manager->shops()->attach($shop->id);
    $manager->roles()->attach($role->id);
    
    expect($manager->managesShop($shop->id))->toBeTrue();
    expect($manager->canAccessPanel(Panel::make()->id('shop')))->toBeTrue();
});
```

---

## 📝 Instructions pour Claude Code

### **Priorités d'Implémentation**

**PRIORITÉ IMMÉDIATE (Phase 1)** :
1. ✅ Compléter modèle `User.php` avec toutes les méthodes
2. ✅ Compléter modèles `Role.php`, `Permission.php`
3. ✅ Compléter modèles `Shop.php`, `Supplier.php`

**PRIORITÉ HAUTE (Phase 2)** :
4. ✅ Modifier migration `user_roles` (ajouter scope + validité)
5. ✅ Créer migration `user_permissions`
6. ✅ Créer migration `context_rules`
7. ✅ Créer migration `activity_logs`

**PRIORITÉ MOYENNE (Phase 3)** :
8. ✅ Créer service `PermissionChecker`
9. ✅ Créer service `ContextRuleEvaluator`
10. ✅ Créer service `ActivityLogger`

**PRIORITÉ NORMALE (Phase 4-5)** :
11. ✅ Créer seeders complets (Roles, Permissions, RolePermissions)
12. ✅ Créer policies (Order, Product, Shop, Supplier, User)

**TESTS (Phase 6)** :
13. ✅ Tests unitaires PermissionChecker
14. ✅ Tests feature multi-rôles et multi-tenancy

### **Comment Procéder**

Pour chaque phase :

1. **Lire attentivement** la documentation dans `/mnt/user-data/outputs/` :
   - `database-schema-roles-permissions-noflaye.md`
   - `multi-roles-panel-switching-noflaye.md`
   - `systeme-autorisations-roles-noflaye.md`

2. **Implémenter** les fichiers un par un

3. **Tester** immédiatement après chaque implémentation :
   ```bash
   php artisan test --filter=NomDuTest
   ```

4. **Formatter** le code :
   ```bash
   vendor/bin/pint
   ```

5. **Migrer** si nécessaire :
   ```bash
   php artisan migrate:fresh --seed
   ```

### **Conventions à Respecter**

- ✅ Utiliser les **return types** explicites
- ✅ Utiliser **PHPDoc** pour documenter
- ✅ Utiliser **Property promotion** dans constructeurs
- ✅ Utiliser les **Enum** pour les types (action_type, permission_type, etc.)
- ✅ Suivre les conventions Laravel Boost
- ✅ Écrire des **tests** pour chaque feature

---

## 🎯 Résultat Attendu

À la fin de l'implémentation, on doit avoir :

✅ **Modèles complets** avec toutes les relations et méthodes
✅ **Migrations complètes** reflétant l'architecture RBAC+GBAC
✅ **Services fonctionnels** pour vérification permissions
✅ **Seeders** avec données de test réalistes
✅ **Policies** pour chaque ressource
✅ **Tests passants** (minimum 80% coverage sur les services)

Le système doit permettre :
- ✅ Multi-rôles (un user = plusieurs rôles)
- ✅ Multi-tenancy (Shop, Supplier)
- ✅ Panel switching (navigation entre panels)
- ✅ Permissions granulaires (grant/revoke)
- ✅ Context rules (évaluation dynamique)
- ✅ Audit trail (logs d'activité)

---

## ❓ Questions & Clarifications

Si tu as besoin de clarifications pendant l'implémentation :

1. **Référence toujours** la documentation dans `/mnt/user-data/outputs/`
2. **Demande confirmation** avant de modifier la structure de base
3. **Propose des alternatives** si tu identifies des problèmes
4. **Signale** les dépendances manquantes

---

## 🚀 Commencer l'Implémentation

**Commande pour démarrer** :

```bash
# Phase 1 - Compléter les modèles
# Commence par User.php, puis Role.php, Permission.php, Shop.php, Supplier.php

# Phase 2 - Migrations
php artisan make:migration add_scope_and_validity_to_user_roles_table --table=user_roles
php artisan make:migration create_user_permissions_table
# ... etc

# Phase 3 - Services
php artisan make:class Services/PermissionChecker
php artisan make:class Services/ContextRuleEvaluator
php artisan make:class Services/ActivityLogger

# Phase 4 - Seeders
php artisan make:seeder RoleSeeder
php artisan make:seeder PermissionSeeder
php artisan make:seeder RolePermissionSeeder

# Phase 5 - Policies
php artisan make:policy OrderPolicy --model=Order
# ... etc

# Phase 6 - Tests
php artisan make:test PermissionCheckerTest --unit
# ... etc
```

Prêt à commencer ? On y va ! 🎉
