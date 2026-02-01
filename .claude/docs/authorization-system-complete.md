# Système d'Autorisation Noflaye MVP - Documentation Complète

## Vue d'Ensemble

Le système d'autorisation de Noflaye MVP est un système **multi-couche** et **multi-tenant** conçu pour gérer les permissions de manière fine à travers 6 panels Filament. Il remplace le système de rôles traditionnel par un système de **Templates de Permission** plus flexible.

---

## Architecture Globale

```
┌─────────────────────────────────────────────────────────────────────┐
│                         UTILISATEUR (User)                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│   ┌──────────────┐    ┌──────────────┐    ┌──────────────────────┐  │
│   │ Permissions  │    │  Templates   │    │    Délégations       │  │
│   │  Directes    │    │ (via pivot)  │    │   Temporaires        │  │
│   └──────────────┘    └──────────────┘    └──────────────────────┘  │
│          │                   │                      │               │
│          └───────────────────┼──────────────────────┘               │
│                              ▼                                      │
│                  ┌─────────────────────┐                            │
│                  │  PermissionChecker  │                            │
│                  │     (Service)       │                            │
│                  └─────────────────────┘                            │
│                              │                                      │
│          ┌───────────────────┼───────────────────┐                  │
│          ▼                   ▼                   ▼                  │
│   ┌──────────────┐   ┌──────────────┐    ┌──────────────┐           │
│   │    Scope     │   │  Wildcards   │    │  Conditions  │           │
│   │ (Contexte)   │   │  (Patterns)  │    │  (Runtime)   │           │
│   └──────────────┘   └──────────────┘    └──────────────┘           │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 1. Entités du Système

### 1.1 Permission (Modèle)

**Fichier:** `app/Models/Permission.php`

Une permission représente une action spécifique sur une ressource.

| Attribut | Type | Description |
|----------|------|-------------|
| `id` | int | Identifiant unique |
| `name` | string | Nom lisible (ex: "Voir les boutiques") |
| `slug` | string | Identifiant technique (ex: "shops.view") |
| `description` | string | Description détaillée |
| `permission_group_id` | int | Groupe de permissions |
| `action_type` | string | Type d'action (view, create, update, delete) |
| `active` | bool | Si la permission est active |
| `is_system` | bool | Si c'est une permission système |

**Relations:**
- `templates()` → BelongsToMany → PermissionTemplate
- `users()` → BelongsToMany → User (permissions directes)
- `group()` → BelongsTo → PermissionGroup

---

### 1.2 Permission Enum (Type-Safe)

**Fichier:** `app/Enums/Permission.php`

Enum PHP 8.1+ pour les permissions type-safe. Évite les erreurs de frappe.

```php
enum Permission: string
{
    // Permissions Utilisateurs
    case USER_VIEW_ANY = 'users.viewAny';
    case USER_VIEW = 'users.view';
    case USER_CREATE = 'users.create';
    case USER_UPDATE = 'users.update';
    case USER_DELETE = 'users.delete';

    // Permissions Boutiques
    case SHOP_VIEW_ANY = 'shops.viewAny';
    case SHOP_VIEW = 'shops.view';
    case SHOP_CREATE = 'shops.create';
    case SHOP_UPDATE = 'shops.update';
    case SHOP_DELETE = 'shops.delete';
    case SHOP_MANAGE_STAFF = 'shops.manageStaff';

    // Permissions Cuisines
    case KITCHEN_VIEW_ANY = 'kitchens.viewAny';
    case KITCHEN_VIEW = 'kitchens.view';
    // ... etc.

    // Permissions Chauffeurs
    case DRIVER_VIEW_ANY = 'drivers.viewAny';
    case DRIVER_ASSIGN = 'drivers.assign';
    // ... etc.

    // Permissions Superviseurs
    case SUPERVISOR_VIEW_ANY = 'supervisors.viewAny';
    case SUPERVISOR_ASSIGN = 'supervisors.assign';
    // ... etc.

    // Permissions Fournisseurs
    case SUPPLIER_VIEW_ANY = 'suppliers.viewAny';
    case SUPPLIER_MANAGE = 'suppliers.manage';
    // ... etc.

    // Méthodes utilitaires
    public function label(): string;           // Nom lisible
    public function resource(): string;        // Ressource (shops, users...)
    public function action(): string;          // Action (view, create...)
    public function isDestructive(): bool;     // delete, forceDelete
    public static function forResource(string $resource): array;
}
```

---

### 1.3 PermissionTemplate (Remplace les Rôles)

**Fichier:** `app/Models/PermissionTemplate.php`

Un template est un **groupe de permissions réutilisable** avec support de hiérarchie.

| Attribut | Type | Description |
|----------|------|-------------|
| `id` | int | Identifiant unique |
| `name` | string | Nom (ex: "Shop Manager") |
| `slug` | string | Identifiant (ex: "shop_manager") |
| `description` | string | Description du template |
| `parent_id` | int | Template parent (héritage) |
| `scope_id` | int | Scope optionnel |
| `level` | int | Niveau dans la hiérarchie |
| `is_active` | bool | Si le template est actif |
| `is_system` | bool | Template système (non modifiable) |
| `auto_sync_users` | bool | Synchronisation automatique |

**Relations:**
- `permissions()` → BelongsToMany → Permission
- `wildcards()` → BelongsToMany → PermissionWildcard
- `users()` → BelongsToMany → User
- `parent()` → BelongsTo → PermissionTemplate (héritage)
- `children()` → HasMany → PermissionTemplate

**Héritage de permissions:**
```php
public function getAllPermissions(): Collection
{
    $permissions = $this->permissions;

    if ($this->parent_id) {
        // Hérite des permissions du parent
        $permissions = $permissions->merge($this->parent->getAllPermissions());
    }

    return $permissions->unique('id');
}
```

**Templates prédéfinis:**

| Slug | Description | Accès Panels |
|------|-------------|--------------|
| `super_admin` | Super administrateur | Tous |
| `admin` | Administrateur | Tous |
| `shop_manager` | Gestionnaire boutique | Shop |
| `shop_staff` | Staff boutique | Shop |
| `kitchen_manager` | Gestionnaire cuisine | Kitchen |
| `kitchen_staff` | Staff cuisine | Kitchen |
| `driver` | Chauffeur | Driver |
| `supplier_manager` | Gestionnaire fournisseur | Supplier |
| `supplier_staff` | Staff fournisseur | Supplier |
| `supervisor_manager` | Gestionnaire supervision | Supervisor |
| `supervisor_staff` | Staff supervision | Supervisor |

---

### 1.4 PermissionWildcard (Patterns)

**Fichier:** `app/Models/PermissionWildcard.php`

Permet de définir des permissions avec des patterns génériques.

| Pattern | Description | Exemple |
|---------|-------------|---------|
| `*.*` | Toutes les permissions | Super admin |
| `shops.*` | Toutes les actions sur shops | Shop manager |
| `*.view` | Vue sur toutes ressources | Lecteur global |
| `*.viewAny` | Liste toutes ressources | Lecteur global |

**Fonctionnement:**
```php
// Dans WildcardExpander::expand()
if ($pattern === '*.*') {
    return Permission::all();
}

if (str_ends_with($pattern, '.*')) {
    $resource = str_replace('.*', '', $pattern);
    return Permission::where('slug', 'like', "{$resource}.%")->get();
}

if (str_starts_with($pattern, '*.')) {
    $action = str_replace('*.', '', $pattern);
    return Permission::where('slug', 'like', "%.{$action}")->get();
}
```

---

### 1.5 Scope (Contexte Multi-Tenant)

**Fichier:** `app/Models/Scope.php`

Un scope définit le **contexte** dans lequel une permission est valide (Shop, Kitchen, etc.).

| Attribut | Type | Description |
|----------|------|-------------|
| `id` | int | Identifiant unique |
| `scopable_type` | string | Type du modèle (App\Models\Shop) |
| `scopable_id` | int | ID du modèle |
| `scope_key` | string | Clé unique du scope |
| `name` | string | Nom du scope |
| `is_active` | bool | Si le scope est actif |

**Relation polymorphique:**
```php
public function scopable(): MorphTo
{
    return $this->morphTo(); // Shop, Kitchen, Driver, etc.
}
```

**Utilisation:**
```php
// Permission limitée à une boutique spécifique
$user->hasPermission('shops.update', $shopScope);

// Permission globale (pas de scope)
$user->hasPermission('users.viewAny');
```

---

### 1.6 PermissionDelegation (Permissions Temporaires)

**Fichier:** `app/Models/PermissionDelegation.php`

Permet de **déléguer temporairement** des permissions à un autre utilisateur.

| Attribut | Type | Description |
|----------|------|-------------|
| `delegator_id` | int | Utilisateur qui délègue |
| `delegatee_id` | int | Utilisateur qui reçoit |
| `permission_id` | int | Permission déléguée |
| `permission_slug` | string | Slug de la permission |
| `scope_id` | int | Scope optionnel |
| `valid_from` | datetime | Début de validité |
| `valid_until` | datetime | Fin de validité |
| `can_redelegate` | bool | Peut re-déléguer |
| `max_redelegation_depth` | int | Profondeur max de re-délégation |
| `revoked_at` | datetime | Date de révocation |

**Exemple d'utilisation:**
```php
// Le manager délègue la gestion à un staff pendant ses vacances
PermissionDelegation::create([
    'delegator_id' => $manager->id,
    'delegatee_id' => $staff->id,
    'permission_slug' => 'shops.manageStaff',
    'valid_from' => now(),
    'valid_until' => now()->addDays(14),
    'can_redelegate' => false,
]);
```

---

## 2. User Model - Méthodes d'Autorisation

**Fichier:** `app/Models/User.php`

### 2.1 Relations Principales

```php
// Templates assignés
public function templates(): BelongsToMany;
public function primaryTemplate(): BelongsTo;

// Permissions directes
public function permissions(): BelongsToMany;
public function directPermissions(): BelongsToMany;

// Entités gérées (tenants)
public function shops(): BelongsToMany;
public function kitchens(): BelongsToMany;
public function drivers(): BelongsToMany;
public function suppliers(): BelongsToMany;
public function supervisors(): BelongsToMany;

// Délégations
public function delegationsGiven(): HasMany;
public function delegationsReceived(): HasMany;
```

### 2.2 Vérification des Templates

```php
// Vérifie un template spécifique
$user->hasTemplate('shop_manager'); // true/false

// Vérifie si l'utilisateur a AU MOINS UN template
$user->hasAnyTemplate(['shop_manager', 'shop_staff']); // true/false

// Vérifie si l'utilisateur a TOUS les templates
$user->hasAllTemplates(['admin', 'shop_manager']); // true/false

// Récupère tous les slugs des templates
$user->getTemplateSlugs(); // ['shop_manager', 'kitchen_staff']
```

### 2.3 Vérification des Permissions

```php
// Vérifie une permission (utilise PermissionChecker)
$user->hasPermission('shops.view'); // true/false
$user->hasPermission('shops.view', $scope); // avec scope

// Vérifie si l'utilisateur a AU MOINS UNE permission
$user->hasAnyPermission(['shops.view', 'shops.update']); // true/false

// Récupère toutes les permissions effectives
$user->getAllPermissions(); // Collection de permissions
$user->getEffectivePermissions($scope); // avec scope
```

### 2.4 Accès aux Panels Filament

```php
public function canAccessPanel(Panel $panel): bool
{
    $panelId = $panel->getId();

    // Super admins accèdent à tout
    if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
        return true;
    }

    return match ($panelId) {
        'admin' => $this->hasAnyTemplate(['super_admin', 'admin']),
        'shop' => $this->hasAnyTemplate(['shop_manager', 'shop_staff'])
                  || $this->shops()->exists(),
        'kitchen' => $this->hasAnyTemplate(['kitchen_manager', 'kitchen_staff'])
                     || $this->kitchens()->exists(),
        'driver' => $this->hasTemplate('driver')
                    || $this->drivers()->exists(),
        'supplier' => $this->hasAnyTemplate(['supplier_manager', 'supplier_staff'])
                      || $this->suppliers()->exists(),
        'supervisor' => $this->hasAnyTemplate(['supervisor_manager', 'supervisor_staff'])
                        || $this->supervisors()->exists(),
        default => false,
    };
}
```

### 2.5 Multi-Tenancy

```php
// Récupère les tenants accessibles par panel
public function getTenants(Panel $panel): Collection
{
    return match ($panel->getId()) {
        'shop' => $this->hasAnyTemplate(['super_admin', 'admin'])
                  ? Shop::all() : $this->shops,
        'kitchen' => $this->hasAnyTemplate(['super_admin', 'admin'])
                     ? Kitchen::all() : $this->kitchens,
        // ... etc.
    };
}

// Vérifie l'accès à un tenant spécifique
public function canAccessTenant(Model $tenant): bool
{
    if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
        return true;
    }

    if ($tenant instanceof Shop) {
        return $this->managesShop($tenant->id);
    }
    // ... etc.
}
```

---

## 3. PermissionChecker Service

**Fichier:** `app/Services/Permissions/PermissionChecker.php`

Service central de vérification des permissions avec **3 niveaux de vérification**.

### 3.1 Flux de Vérification

```
┌─────────────────────────────────────────────────────────────┐
│                    userHasPermission()                      │
│                           │                                 │
│                           ▼                                 │
│     ┌─────────────────────────────────────────────────┐     │
│     │           CACHE (Redis/File) 1h                 │     │
│     │     "permission.{userId}.{slug}.{scopeId}"      │     │
│     └─────────────────────────────────────────────────┘     │
│                           │ miss                            │
│                           ▼                                 │
│     ┌─────────────────────────────────────────────────┐     │
│     │              checkWithScope()                   │     │
│     │                     │                           │     │
│     │     ┌───────────────┼───────────────┐           │     │
│     │     ▼               ▼               ▼           │     │
│     │  Direct        Template       Delegated         │     │
│     │ Permission    Permission     Permission         │     │
│     └─────────────────────────────────────────────────┘     │
│                           │                                 │
│                           ▼                                 │
│     ┌─────────────────────────────────────────────────┐     │
│     │          Condition Evaluation                   │     │
│     │        (si conditions définies)                 │     │
│     └─────────────────────────────────────────────────┘     │
│                           │                                 │
│                           ▼                                 │
│                    true / false                             │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 Vérification des Permissions Directes

```php
private function hasDirectPermission(User $user, string $permissionSlug, ?Scope $scope): bool
{
    $userPerm = $user->permissions()
        ->where('slug', $permissionSlug)
        ->when($scope, fn ($q) => $q->where('user_permissions.scope_id', $scope->id))
        ->first();

    if (! $userPerm) {
        return false;
    }

    // Évalue les conditions si présentes
    $conditions = $userPerm->pivot->conditions ?? [];
    if (! empty($conditions)) {
        return $this->conditionEvaluator->evaluate($conditions, $user);
    }

    return true;
}
```

### 3.3 Vérification via Templates

```php
private function hasTemplatePermission(User $user, string $permissionSlug, ?Scope $scope): bool
{
    $templates = $user->templates()
        ->when($scope, fn ($q) => $q->where('user_templates.scope_id', $scope->id))
        ->with(['permissions', 'wildcards'])
        ->get();

    foreach ($templates as $template) {
        // 1. Vérification directe dans les permissions du template
        if ($template->permissions->contains('slug', $permissionSlug)) {
            return true;
        }

        // 2. Vérification via wildcards
        foreach ($template->wildcards as $wildcard) {
            $permission = Permission::where('slug', $permissionSlug)->first();
            if ($permission && $this->wildcardExpander->matchesPattern($permission, $wildcard->pattern)) {
                return true;
            }
        }
    }

    return false;
}
```

### 3.4 Vérification via Délégation

```php
public function hasDelegatedPermission(User $user, string $permissionSlug, ?Scope $scope): bool
{
    $delegation = PermissionDelegation::active()
        ->where('delegatee_id', $user->id)
        ->where('permission_slug', $permissionSlug)
        ->when($scope, fn ($q) => $q->where('scope_id', $scope->id))
        ->first();

    return $delegation !== null;
}
```

---

## 4. Policies Laravel

**Dossier:** `app/Policies/`

### 4.1 Trait ChecksPermissions

**Fichier:** `app/Policies/Concerns/ChecksPermissions.php`

```php
trait ChecksPermissions
{
    protected array $cachedPermissions = [];

    protected function can(User $user, PermissionEnum $permission, ?int $scopeId = null): bool
    {
        $cacheKey = "{$user->id}:{$permission->value}:{$scopeId}";

        if (array_key_exists($cacheKey, $this->cachedPermissions)) {
            return $this->cachedPermissions[$cacheKey];
        }

        $result = app(PermissionChecker::class)->userHasPermission(
            userId: $user->id,
            permission: $permission->value,
            scopeId: $scopeId
        );

        $this->cachedPermissions[$cacheKey] = $result;
        return $result;
    }

    protected function canAny(User $user, array $permissions, ?int $scopeId = null): bool;
    protected function canAll(User $user, array $permissions, ?int $scopeId = null): bool;
    protected function preloadPermissions(User $user, array $permissions, ?int $scopeId = null): void;
}
```

### 4.2 Exemple: ShopPolicy

```php
class ShopPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::SHOP_VIEW_ANY);
    }

    public function view(User $user, Shop $shop): bool
    {
        // Permission globale OU permission scopée
        if ($this->can($user, Permission::SHOP_VIEW)) {
            return true;
        }
        return $this->can($user, Permission::SHOP_VIEW, $shop->id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::SHOP_CREATE);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_UPDATE)
            || $this->can($user, Permission::SHOP_UPDATE, $shop->id);
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_DELETE);
    }

    public function manageStaff(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_MANAGE_STAFF, $shop->id);
    }
}
```

### 4.3 Policies Enregistrées

**Fichier:** `app/Providers/AuthServiceProvider.php`

```php
protected $policies = [
    User::class => UserPolicy::class,
    Shop::class => ShopPolicy::class,
    Kitchen::class => KitchenPolicy::class,
    Driver::class => DriverPolicy::class,
    Supervisor::class => SupervisorPolicy::class,
    Supplier::class => SupplierPolicy::class,
    Permission::class => PermissionPolicy::class,
    PermissionTemplate::class => TemplatePolicy::class,
];
```

---

## 5. AuthServiceProvider - Gate Before

**Fichier:** `app/Providers/AuthServiceProvider.php`

Le `Gate::before` intercepte **toutes les vérifications d'autorisation** avant les policies.

```php
Gate::before(function (User $user, string $ability) {
    // 1. Super Admin = accès total
    if ($user->primaryTemplate?->slug === 'admin') {
        return true;
    }

    // 2. Vérification via système de permissions
    return $this->checkUserPermission($user, $ability);
});
```

### Conversion Filament → Permission Slug

```php
protected function convertAbilityToPermissionSlug(string $ability): string
{
    // 'view_any_user' → 'users.list'
    // 'create_shop' → 'shops.create'
    // 'update_kitchen' → 'kitchens.update'

    $actionMap = [
        'view_any' => 'list',
        'view' => 'read',
        'create' => 'create',
        'update' => 'update',
        'delete' => 'delete',
        'restore' => 'update',
        'force_delete' => 'delete',
    ];

    // ... logique de conversion
}
```

---

## 6. Schéma Base de Données

### Tables Principales

```
users
├── id
├── name
├── email
├── primary_template_id → permission_templates.id
└── ...

permission_templates
├── id
├── name
├── slug
├── parent_id → permission_templates.id (héritage)
├── scope_id → scopes.id
├── level
├── is_active
├── is_system
└── auto_sync_users

permissions
├── id
├── name
├── slug
├── permission_group_id
├── action_type
├── active
└── is_system

permission_wildcards
├── id
├── pattern
├── pattern_type
├── is_active
└── auto_expand

scopes
├── id
├── scopable_type (App\Models\Shop, etc.)
├── scopable_id
├── scope_key
└── is_active

permission_delegations
├── id
├── delegator_id → users.id
├── delegatee_id → users.id
├── permission_id → permissions.id
├── permission_slug
├── scope_id → scopes.id
├── valid_from
├── valid_until
├── can_redelegate
├── revoked_at
└── revoked_by
```

### Tables Pivot

```
user_templates
├── user_id → users.id
├── permission_template_id → permission_templates.id
├── scope_id → scopes.id
├── template_version
├── auto_upgrade
├── auto_sync
├── valid_from
└── valid_until

user_permissions (permissions directes)
├── user_id → users.id
├── permission_id → permissions.id
├── scope_id → scopes.id
├── expires_at
├── source (direct, template, delegation)
├── source_id
└── conditions (JSON)

template_permissions
├── permission_template_id → permission_templates.id
├── permission_id → permissions.id
├── source
├── wildcard_id
└── sort_order

template_wildcards
├── permission_template_id → permission_templates.id
├── permission_wildcard_id → permission_wildcards.id
└── sort_order
```

---

## 7. Implications et Bonnes Pratiques

### 7.1 Performance

1. **Cache**: Le PermissionChecker utilise un cache Redis/File de 1h
2. **Cache Request-Level**: Le trait ChecksPermissions cache dans la requête
3. **Eager Loading**: Charger les relations permissions et wildcards si nécessaire
4. **Invalider le cache** après modification de permissions:
   ```php
   $permissionChecker->invalidateUserCache($user);
   ```

### 7.2 Sécurité

1. **Toujours utiliser les policies** - Filament les observe automatiquement
2. **Scoper les permissions** quand possible pour limiter l'accès
3. **Les délégations expirent** - définir toujours `valid_until`
4. **Permissions système** - ne pas modifier les permissions `is_system = true`

### 7.3 Multi-Tenancy

1. **Chaque panel a ses propres tenants** (Shop, Kitchen, etc.)
2. **Le scope lie une permission à un contexte** spécifique
3. **Les admins voient tous les tenants**, les autres seulement les leurs
4. **Vérifier l'accès au tenant** avant toute opération

### 7.4 Ajout d'une Nouvelle Permission

1. Ajouter la case dans l'enum `Permission`:
   ```php
   case NEW_RESOURCE_ACTION = 'new_resources.action';
   ```

2. Créer la permission en base (seeder ou migration)

3. L'assigner aux templates appropriés

4. Utiliser dans la policy:
   ```php
   public function newAction(User $user): bool
   {
       return $this->can($user, Permission::NEW_RESOURCE_ACTION);
   }
   ```

### 7.5 Debugging

```php
// Voir toutes les permissions d'un utilisateur
$user->getAllPermissions();

// Vérifier pourquoi une permission est refusée
Log::debug('Permission check', [
    'user' => $user->id,
    'permission' => $slug,
    'direct' => $user->permissions()->where('slug', $slug)->exists(),
    'templates' => $user->templates->pluck('slug'),
    'delegations' => $user->delegationsReceived()->active()->pluck('permission_slug'),
]);
```

---

## 8. Diagramme des Relations

```
                    ┌──────────────────┐
                    │      User        │
                    └────────┬─────────┘
                             │
         ┌───────────────────┼───────────────────┐
         │                   │                   │
         ▼                   ▼                   ▼
┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│ user_templates │  │user_permissions│  │ permission_    │
│    (pivot)     │  │    (pivot)     │  │ delegations    │
└───────┬────────┘  └───────┬────────┘  └───────┬────────┘
        │                   │                   │
        ▼                   ▼                   ▼
┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│  Permission    │  │   Permission   │  │   Permission   │
│   Template     │  │                │  │                │
└───────┬────────┘  └────────────────┘  └────────────────┘
        │
        ├──────────────────┐
        │                  │
        ▼                  ▼
┌────────────────┐  ┌────────────────┐
│template_       │  │template_       │
│permissions     │  │wildcards       │
│   (pivot)      │  │   (pivot)      │
└───────┬────────┘  └───────┬────────┘
        │                   │
        ▼                   ▼
┌────────────────┐  ┌────────────────┐
│   Permission   │  │ Permission    │
│                │  │  Wildcard     │
└────────────────┘  └────────────────┘
```

---

## 9. Résumé

Le système d'autorisation Noflaye MVP offre:

| Fonctionnalité | Description |
|----------------|-------------|
| **Templates** | Groupes de permissions réutilisables avec héritage |
| **Wildcards** | Patterns pour permissions en masse (`shops.*`, `*.view`) |
| **Scopes** | Permissions limitées à un contexte (Shop, Kitchen...) |
| **Délégations** | Permissions temporaires entre utilisateurs |
| **Cache** | Multi-niveaux (Redis + Request) pour la performance |
| **Type-Safe** | Enum PHP pour éviter les erreurs de frappe |
| **Multi-Panel** | Contrôle d'accès par panel Filament |
| **Multi-Tenant** | Isolation des données par tenant |
