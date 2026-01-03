# 🔐 Système de Permissions Type-Safe

Documentation complète du système de permissions type-safe de NoFlaye MVP.

## Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Enums](#enums)
4. [DTOs (Data Transfer Objects)](#dtos-data-transfer-objects)
5. [Actions](#actions)
6. [Policies](#policies)
7. [Middleware](#middleware)
8. [Commandes Artisan](#commandes-artisan)
9. [Exemples d'utilisation](#exemples-dutilisation)
10. [Meilleures Pratiques](#meilleures-pratiques)

---

## Vue d'ensemble

Le système de permissions de NoFlaye utilise une approche type-safe basée sur des enums PHP 8.1+, des DTOs avec Spatie Laravel Data, des Actions avec Lorisleiva, et des Policies Laravel.

### Caractéristiques Principales

- ✅ **Type-safe** : Utilise des enums pour la sécurité au niveau compilation
- ✅ **Performant** : Cache au niveau requête et Redis
- ✅ **Auditable** : Logs d'activité complets avec IP et user agent
- ✅ **Idempotent** : Actions sûres pour les retries
- ✅ **Documenté** : PHPDoc complet avec exemples
- ✅ **Testable** : Architecture découplée facilitant les tests

---

## Architecture

### Schéma des Composants

```
┌─────────────────────────────────────────────────────┐
│                    Routes/Controllers                │
│                   (point d'entrée)                   │
└───────────────────┬─────────────────────────────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
        ▼                       ▼
┌──────────────┐        ┌──────────────┐
│  Middleware  │        │   Policies   │
│ (CheckPerm)  │        │ (autoriser)  │
└──────┬───────┘        └──────┬───────┘
       │                       │
       │  ┌────────────────────┘
       │  │
       ▼  ▼
┌─────────────────┐
│ PermissionChecker│
│   (service)      │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐        ┌──────────────┐
│    Actions      │◄───────│     DTOs     │
│ (operations)    │        │ (validation) │
└────────┬────────┘        └──────────────┘
         │
         ▼
┌─────────────────┐
│   Enums         │
│ (définitions)   │
└─────────────────┘
```

---

## Enums

### Permission Enum

Définit toutes les permissions du système (72 permissions).

**Fichier** : `app/Enums/Permission.php`

```php
use App\Enums\Permission;

// Exemples de permissions
Permission::USER_VIEW_ANY;      // 'users.viewAny'
Permission::SHOP_VIEW;          // 'shops.view'
Permission::KITCHEN_UPDATE;     // 'kitchens.update'
```

#### Méthodes Utiles

```php
// Obtenir toutes les permissions d'une ressource
$userPerms = Permission::forResource('users');
// [USER_VIEW_ANY, USER_VIEW, USER_CREATE, ...]

// Vérifier si une permission existe
if (Permission::exists('users.view')) {
    // Permission définie dans l'enum
}

// Convertir string en enum ou retourner le string
$perm = Permission::fromString('users.view');
// Returns Permission::USER_VIEW enum

$custom = Permission::fromString('custom.permission');
// Returns 'custom.permission' string

// Obtenir un label lisible
Permission::USER_VIEW_ANY->label();
// "View Any Users"

// Extraire resource et action
Permission::SHOP_UPDATE->resource(); // 'shops'
Permission::SHOP_UPDATE->action();   // 'update'

// Vérifier le type de permission
Permission::USER_VIEW_ANY->isViewAny();      // true
Permission::USER_DELETE->isDestructive();     // true
```

### Template Enum

Définit les templates de rôles (10 templates).

**Fichier** : `app/Enums/Template.php`

```php
use App\Enums\Template;

Template::SUPER_ADMIN;
Template::ADMIN;
Template::SHOP_MANAGER;
Template::KITCHEN_MANAGER;
Template::DRIVER;
```

#### Méthodes Utiles

```php
// Label localisé
Template::SHOP_MANAGER->label();
// "Gérant de Boutique"

// Panel Filament associé
Template::SHOP_MANAGER->panel();
// "shop"

// Permissions par défaut
$perms = Template::SHOP_MANAGER->defaultPermissions();
// [Permission::SHOP_VIEW, Permission::SHOP_UPDATE, ...]

// Vérifier niveau admin
Template::ADMIN->isAdmin(); // true

// Vérifier capacité de gestion
Template::SHOP_MANAGER->canManage('shop'); // true
```

### EntityType Enum

Mappe les types d'entités aux modèles.

**Fichier** : `app/Enums/EntityType.php`

```php
use App\Enums\EntityType;

// Classe du modèle
EntityType::USER->modelClass();
// \App\Models\User::class

// Label
EntityType::SHOP->label();
// "Boutique"

// Forme plurielle
EntityType::SHOP->plural();
// "shops"
```

### RequestStatus Enum

Gère les statuts de demandes.

**Fichier** : `app/Enums/RequestStatus.php`

```php
use App\Enums\RequestStatus;

RequestStatus::PENDING->label();    // "En attente"
RequestStatus::PENDING->color();    // "warning"
RequestStatus::PENDING->icon();     // "heroicon-o-clock"

// Transitions d'état
RequestStatus::PENDING->canTransitionTo(RequestStatus::APPROVED);
// true

RequestStatus::APPROVED->isFinal(); // true
```

---

## DTOs (Data Transfer Objects)

### AssignPermissionData

Pour assigner une permission à un utilisateur.

**Fichier** : `app/Data/Permissions/AssignPermissionData.php`

```php
use App\Data\Permissions\AssignPermissionData;
use App\Enums\Permission;
use Carbon\Carbon;

$data = new AssignPermissionData(
    user_id: 123,
    permission: Permission::SHOP_VIEW,
    scope_id: 456,              // Optionnel : pour permissions scopées
    valid_from: now(),           // Optionnel : date de début
    valid_until: now()->addYear(), // Optionnel : date de fin
    source: 'direct',            // 'direct', 'template', 'delegation', 'import'
    reason: 'Promotion manager'  // Optionnel : raison pour audit
);

// Helper method
$slug = $data->permissionSlug(); // 'shops.view'
```

### RevokePermissionData

Pour révoquer une permission.

**Fichier** : `app/Data/Permissions/RevokePermissionData.php`

```php
use App\Data\Permissions\RevokePermissionData;
use App\Enums\Permission;

$data = new RevokePermissionData(
    user_id: 123,
    permission: Permission::SHOP_VIEW,
    scope_id: 456,
    reason: 'User terminated'
);
```

### AssignTemplateData

Pour assigner un template à un utilisateur.

**Fichier** : `app/Data/Templates/AssignTemplateData.php`

```php
use App\Data\Templates\AssignTemplateData;
use App\Enums\Template;

$data = new AssignTemplateData(
    user_id: 123,
    template: Template::SHOP_MANAGER,
    auto_sync: true,  // Sync automatique des mises à jour de template
    valid_from: now(),
    valid_until: null // Null = jamais expire
);

// Helper method
$slug = $data->templateSlug(); // 'shop_manager'
```

---

## Actions

### AssignPermissionToUser

Assigne une permission avec validation complète.

**Fichier** : `app/Actions/Permissions/AssignPermissionToUser.php`

```php
use App\Actions\Permissions\AssignPermissionToUser;
use App\Data\Permissions\AssignPermissionData;
use App\Enums\Permission;

// Utilisation de base
$data = new AssignPermissionData(
    user_id: $user->id,
    permission: Permission::SHOP_VIEW,
    scope_id: $shop->id
);

$success = AssignPermissionToUser::run($data);
// Returns true si assignée ou déjà existante (idempotent)

// Avec contrôle d'idempotence
$success = AssignPermissionToUser::run($data, skipIfExists: false);
// Returns false si déjà existante (non-idempotent)

// Comme job en arrière-plan
AssignPermissionToUser::dispatch($data);
```

**Caractéristiques** :
- ✅ Idempotent par défaut (skipIfExists = true)
- ✅ Logs de métriques de performance
- ✅ Audit trail avec IP et user agent
- ✅ Invalidation automatique du cache
- ✅ Transaction DB safe

### RevokePermissionFromUser

Révoque une permission avec audit.

**Fichier** : `app/Actions/Permissions/RevokePermissionFromUser.php`

```php
use App\Actions\Permissions\RevokePermissionFromUser;
use App\Data\Permissions\RevokePermissionData;
use App\Enums\Permission;

$data = new RevokePermissionData(
    user_id: $user->id,
    permission: Permission::SHOP_VIEW,
    scope_id: $shop->id,
    reason: 'Manager demoted'
);

$success = RevokePermissionFromUser::run($data);
// Returns true si révoquée ou n'existe pas (idempotent)
```

**Caractéristiques** :
- ✅ Idempotent par défaut (skipIfNotExists = true)
- ✅ Logs de métriques
- ✅ Audit trail complet
- ✅ Cache invalidation

### AssignTemplateToUser

Assigne un template de permissions.

**Fichier** : `app/Actions/Templates/AssignTemplateToUser.php`

```php
use App\Actions\Templates\AssignTemplateToUser;
use App\Data\Templates\AssignTemplateData;
use App\Enums\Template;

$data = new AssignTemplateData(
    user_id: $user->id,
    template: Template::SHOP_MANAGER,
    auto_sync: true
);

$success = AssignTemplateToUser::run($data);
```

---

## Policies

### Trait ChecksPermissions

Trait pour vérifier les permissions dans les policies.

**Fichier** : `app/Policies/Concerns/ChecksPermissions.php`

```php
use App\Policies\Concerns\ChecksPermissions;
use App\Enums\Permission;
use App\Models\User;
use App\Models\Shop;

class ShopPolicy
{
    use ChecksPermissions;

    public function view(User $user, Shop $shop): bool
    {
        // Vérifie permission globale OU scopée
        return $this->can($user, Permission::SHOP_VIEW)
            || $this->can($user, Permission::SHOP_VIEW, $shop->id);
    }

    public function update(User $user, Shop $shop): bool
    {
        // Vérifie l'une des permissions
        return $this->canAny($user, [
            Permission::SHOP_UPDATE,
            Permission::SHOP_MANAGE_STAFF,
        ], $shop->id);
    }

    public function delete(User $user, Shop $shop): bool
    {
        // Vérifie toutes les permissions
        return $this->canAll($user, [
            Permission::SHOP_DELETE,
            Permission::SHOP_VIEW,
        ], $shop->id);
    }

    public function manage(User $user, Shop $shop): bool
    {
        // Précharge permissions pour performance
        $this->preloadPermissions($user, [
            Permission::SHOP_VIEW,
            Permission::SHOP_UPDATE,
            Permission::SHOP_DELETE,
        ], $shop->id);

        return $this->canAll($user, [...], $shop->id);
    }
}
```

**Caractéristiques** :
- ✅ Cache au niveau requête (évite requêtes dupliquées)
- ✅ Méthodes can(), canAny(), canAll()
- ✅ Préchargement batch avec preloadPermissions()
- ✅ Short-circuiting pour performance

### Policies Disponibles

- `UserPolicy` - Gestion des utilisateurs
- `ShopPolicy` - Gestion des boutiques
- `KitchenPolicy` - Gestion des cuisines
- `PermissionPolicy` - Gestion des permissions
- `TemplatePolicy` - Gestion des templates

---

## Middleware

### CheckPermission

Middleware pour protéger les routes.

**Fichier** : `app/Http/Middleware/CheckPermission.php`

**Enregistrement** : Alias `'permission'` dans `bootstrap/app.php`

#### Utilisation

```php
// Route simple
Route::get('/users', [UserController::class, 'index'])
    ->middleware('permission:users.viewAny');

// Route avec scope (utilise paramètre de route)
Route::get('/shops/{shop}/edit', [ShopController::class, 'edit'])
    ->middleware('permission:shops.update,scope:shop');

// OR logic (n'importe quelle permission)
Route::post('/settings', [SettingsController::class, 'store'])
    ->middleware('permission:settings.update|admin.access');

// AND logic (toutes les permissions)
Route::delete('/critical', [CriticalController::class, 'destroy'])
    ->middleware('permission:admin.access&critical.delete');

// Groupes de routes
Route::middleware(['auth', 'permission:shops.viewAny'])->group(function () {
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{shop}', [ShopController::class, 'show'])
        ->middleware('permission:shops.view,scope:shop');
});
```

**Syntaxe** :
- Simple : `permission:users.view`
- OR : `permission:users.view|users.update`
- AND : `permission:users.view&users.update`
- Scope : `permission:shops.view,scope:shop`

---

## Commandes Artisan

### permissions:generate-from-enum

Génère les permissions en base depuis l'enum.

```bash
# Génération normale
php artisan permissions:generate-from-enum

# Dry run (simulation)
php artisan permissions:generate-from-enum --dry-run

# Avec groupe personnalisé
php artisan permissions:generate-from-enum --group="Core Permissions"
```

**Sortie** :
```
🔐 Generating permissions from Permission enum...

📁 Using permission group: System Permissions (ID: 1)

 72/72 [████████████████████████████] 100%

✅ Permission generation completed

┌──────────────────┬───────┐
│ Metric           │ Count │
├──────────────────┼───────┤
│ Created          │ 72    │
│ Updated          │ 0     │
│ Skipped          │ 0     │
│ Total Processed  │ 72    │
└──────────────────┴───────┘
```

---

## Exemples d'utilisation

### 1. Assigner une Permission Scopée

```php
use App\Actions\Permissions\AssignPermissionToUser;
use App\Data\Permissions\AssignPermissionData;
use App\Enums\Permission;

// Donner permission de voir un shop spécifique
$data = new AssignPermissionData(
    user_id: $employee->id,
    permission: Permission::SHOP_VIEW,
    scope_id: $shop->id,
    source: 'direct',
    reason: 'Employee assigned to shop'
);

AssignPermissionToUser::run($data);

// Maintenant l'employé peut voir CE shop uniquement
```

### 2. Assigner un Template

```php
use App\Actions\Templates\AssignTemplateToUser;
use App\Data\Templates\AssignTemplateData;
use App\Enums\Template;

$data = new AssignTemplateData(
    user_id: $manager->id,
    template: Template::SHOP_MANAGER,
    auto_sync: true  // Sync auto des mises à jour de template
);

AssignTemplateToUser::run($data);

// Manager a maintenant toutes les permissions du template
```

### 3. Vérifier Permission dans Controller

```php
use App\Enums\Permission;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function update(Request $request, Shop $shop)
    {
        // Option 1: Via Policy (recommandé)
        $this->authorize('update', $shop);

        // Option 2: Via Gate
        if (! Gate::allows('shop_update', $shop)) {
            abort(403);
        }

        // Option 3: Via PermissionChecker service
        if (! app(PermissionChecker::class)->userHasPermission(
            userId: auth()->id(),
            permission: Permission::SHOP_UPDATE->value,
            scopeId: $shop->id
        )) {
            abort(403);
        }

        // Logique de mise à jour...
    }
}
```

### 4. Protéger Routes

```php
// routes/web.php
use App\Http\Controllers\ShopController;

// Protection simple
Route::get('/shops', [ShopController::class, 'index'])
    ->middleware(['auth', 'permission:shops.viewAny']);

// Protection avec scope
Route::resource('shops', ShopController::class)
    ->middleware('auth')
    ->only(['show', 'edit', 'update', 'destroy'])
    ->middleware('permission:shops.view,scope:shop');
```

### 5. Vérifier Multiples Permissions

```php
use App\Policies\Concerns\ChecksPermissions;

class CustomPolicy
{
    use ChecksPermissions;

    public function complexAction(User $user, Resource $resource): bool
    {
        // Vérifier N'IMPORTE quelle permission (OR)
        if ($this->canAny($user, [
            Permission::RESOURCE_UPDATE,
            Permission::RESOURCE_DELETE,
        ], $resource->id)) {
            return true;
        }

        // Vérifier TOUTES les permissions (AND)
        return $this->canAll($user, [
            Permission::RESOURCE_VIEW,
            Permission::RESOURCE_EXPORT,
        ], $resource->id);
    }
}
```

---

## Meilleures Pratiques

### 1. Toujours Utiliser les Enums

❌ **Mauvais** :
```php
$permission = 'users.view'; // String magique
```

✅ **Bon** :
```php
use App\Enums\Permission;

$permission = Permission::USER_VIEW; // Type-safe, autocomplete
```

### 2. Préférer les Policies au Middleware

❌ **Moins bon** :
```php
Route::post('/shops/{shop}', [...])->middleware('permission:shops.update,scope:shop');
```

✅ **Meilleur** :
```php
// Dans le controller
$this->authorize('update', $shop);
```

**Raison** : Les policies permettent une logique plus complexe et sont testables.

### 3. Utiliser les DTOs pour la Validation

❌ **Mauvais** :
```php
$user->permissions()->attach($permissionId, ['scope_id' => $scopeId]);
```

✅ **Bon** :
```php
$data = new AssignPermissionData(...);
AssignPermissionToUser::run($data);
```

**Raison** : Validation automatique, audit trail, idempotence.

### 4. Précharger pour Performance

```php
// Dans une policy avec plusieurs vérifications
public function complexCheck(User $user, Shop $shop): bool
{
    // Précharger toutes les permissions qu'on va vérifier
    $this->preloadPermissions($user, [
        Permission::SHOP_VIEW,
        Permission::SHOP_UPDATE,
        Permission::SHOP_DELETE,
    ], $shop->id);

    // Maintenant ces vérifications utilisent le cache
    return $this->canAll($user, [...], $shop->id);
}
```

### 5. Documenter les Raisons

```php
$data = new RevokePermissionData(
    user_id: $user->id,
    permission: Permission::SHOP_MANAGE_STAFF,
    scope_id: $shop->id,
    reason: 'Manager transferred to another shop' // ✅ Audit trail
);
```

### 6. Tester avec Dry-Run

```bash
# Toujours tester d'abord avec --dry-run
php artisan permissions:generate-from-enum --dry-run

# Puis exécuter réellement
php artisan permissions:generate-from-enum
```

### 7. Gérer les Permissions Temporaires

```php
$data = new AssignPermissionData(
    user_id: $user->id,
    permission: Permission::SHOP_UPDATE,
    scope_id: $shop->id,
    valid_from: now(),
    valid_until: now()->addDays(30), // Expire dans 30 jours
    reason: 'Temporary manager replacement'
);
```

---

## Performance

### Cache Multi-niveaux

1. **Request-level** (ChecksPermissions trait) :
   - Cache dans `$cachedPermissions` array
   - Durée : Request actuelle
   - Évite requêtes dupliquées dans une policy

2. **Redis** (PermissionChecker service) :
   - Cache avec tags : `['permissions', 'user.{id}']`
   - Durée : 1 heure
   - Invalidé automatiquement par les Actions

### Optimisations

```php
// ✅ Bon : Précharge en une fois
$this->preloadPermissions($user, [
    Permission::SHOP_VIEW,
    Permission::SHOP_UPDATE,
    Permission::SHOP_DELETE,
], $shop->id);

// ❌ Mauvais : Vérifie une par une
foreach ($permissions as $perm) {
    $this->can($user, $perm, $shop->id); // N requêtes
}
```

---

## Sécurité

### Audit Trail

Toutes les actions de permissions sont loggées avec :
- ✅ User qui effectue l'action
- ✅ IP address
- ✅ User agent
- ✅ Timestamp
- ✅ Raison (optionnelle)

```php
// Exemple de log d'activité
activity()
    ->performedOn($user)
    ->causedBy(Auth::user())
    ->withProperties([
        'permission' => 'shops.update',
        'scope_id' => 456,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0...',
        'reason' => 'Promotion to manager',
    ])
    ->log('permission_granted');
```

### Validation

Tous les DTOs utilisent les attributs de validation Spatie Laravel Data :

```php
#[Required]
#[WithCast(EnumCast::class)]
public PermissionEnum $permission;
```

---

## Tests

### Tester les Policies

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Enums\Permission;
use App\Actions\Permissions\AssignPermissionToUser;
use App\Data\Permissions\AssignPermissionData;

it('allows user with permission to view shop', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create();

    // Assigner permission
    AssignPermissionToUser::run(new AssignPermissionData(
        user_id: $user->id,
        permission: Permission::SHOP_VIEW,
        scope_id: $shop->id
    ));

    // Vérifier policy
    expect($user->can('view', $shop))->toBeTrue();
});

it('denies user without permission', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create();

    expect($user->cannot('view', $shop))->toBeTrue();
});
```

### Tester les Actions

```php
it('assigns permission idempotently', function () {
    $user = User::factory()->create();
    $data = new AssignPermissionData(
        user_id: $user->id,
        permission: Permission::USER_VIEW
    );

    // Première assignation
    $result1 = AssignPermissionToUser::run($data);
    expect($result1)->toBeTrue();

    // Deuxième assignation (idempotent)
    $result2 = AssignPermissionToUser::run($data);
    expect($result2)->toBeTrue();

    // Vérifie qu'il n'y a qu'une seule entrée
    expect($user->permissions()->count())->toBe(1);
});
```

---

## Troubleshooting

### Permission Non Reconnue

**Problème** : `Permission not found: custom.action`

**Solution** : Utiliser `Permission::fromString()` pour supporter les permissions custom :

```php
$perm = Permission::fromString('custom.action');
// Retourne la string si non dans l'enum
```

### Cache Non Invalidé

**Problème** : Changements de permissions non reflétés

**Solution** : Vérifier que les tags de cache sont corrects :

```php
Cache::tags(['users', "user.{$userId}", 'permissions'])->flush();
```

### Policy Non Appliquée

**Problème** : Gate::before retourne toujours true

**Solution** : Vérifier `AuthServiceProvider` :

```php
// Dans AuthServiceProvider
Gate::before(function (User $user, string $ability) {
    if ($user->primaryTemplate?->slug === 'admin') {
        return true; // Admin bypass
    }

    return $this->checkUserPermission($user, $ability);
});
```

---

## Migration depuis Ancien Système

### 1. Générer Permissions

```bash
php artisan permissions:generate-from-enum
```

### 2. Migrer Templates

```php
// Créer templates depuis enum
foreach (Template::cases() as $template) {
    PermissionTemplate::firstOrCreate(
        ['slug' => $template->value],
        ['name' => $template->label()]
    );
}
```

### 3. Assigner Permissions aux Templates

```php
use App\Enums\Template;

foreach (Template::cases() as $template) {
    $dbTemplate = PermissionTemplate::where('slug', $template->value)->first();
    $permissions = $template->defaultPermissions();

    foreach ($permissions as $perm) {
        $dbPerm = Permission::where('slug', $perm->value)->first();
        $dbTemplate->permissions()->syncWithoutDetaching($dbPerm);
    }
}
```

---

## Support

Pour toute question ou problème :
1. Consulter cette documentation
2. Vérifier les PHPDoc dans le code
3. Regarder les exemples dans les tests
4. Contacter l'équipe dev

---

**Version** : 1.0.0
**Dernière mise à jour** : 2026-01-03
**Auteur** : Équipe NoFlaye
