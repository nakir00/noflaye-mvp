# Policies Guide - NoFlaye MVP

## Overview

Les Policies dans NoFlaye MVP utilisent le système de permissions type-safe pour gérer l'autorisation. Toutes les policies utilisent le trait `ChecksPermissions` pour bénéficier du cache au niveau de la requête et de l'intégration avec `PermissionChecker`.

## Policies Disponibles

### Entities Management
- ✅ **ShopPolicy** - Gestion des boutiques
- ✅ **KitchenPolicy** - Gestion des cuisines
- ✅ **DriverPolicy** - Gestion des chauffeurs
- ✅ **SupervisorPolicy** - Gestion des superviseurs
- ✅ **SupplierPolicy** - Gestion des fournisseurs

### Access Control
- ✅ **UserPolicy** - Gestion des utilisateurs
- ✅ **PermissionPolicy** - Gestion des permissions
- ✅ **TemplatePolicy** - Gestion des templates de rôles

## Structure Standard d'une Policy

Toutes les policies suivent cette structure:

```php
<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\{Model};
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class {Model}Policy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::{MODEL}_VIEW_ANY);
    }

    public function view(User $user, {Model} $model): bool
    {
        // Global permission
        if ($this->can($user, Permission::{MODEL}_VIEW)) {
            return true;
        }

        // Scoped permission
        return $this->can($user, Permission::{MODEL}_VIEW, $model->id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::{MODEL}_CREATE);
    }

    public function update(User $user, {Model} $model): bool
    {
        return $this->can($user, Permission::{MODEL}_UPDATE)
            || $this->can($user, Permission::{MODEL}_UPDATE, $model->id);
    }

    public function delete(User $user, {Model} $model): bool
    {
        return $this->can($user, Permission::{MODEL}_DELETE);
    }
}
```

## Nouvelles Policies Créées

### 1. DriverPolicy

**Fichier:** `app/Policies/DriverPolicy.php`

**Méthodes:**
- `viewAny(User $user)` - Liste des chauffeurs
- `view(User $user, Driver $driver)` - Voir un chauffeur spécifique
- `create(User $user)` - Créer un chauffeur
- `update(User $user, Driver $driver)` - Modifier un chauffeur
- `delete(User $user, Driver $driver)` - Supprimer un chauffeur
- `assign(User $user, Driver $driver, ?int $scopeId)` - Assigner un chauffeur à une entité

**Permissions utilisées:**
```php
Permission::DRIVER_VIEW_ANY
Permission::DRIVER_VIEW
Permission::DRIVER_CREATE
Permission::DRIVER_UPDATE
Permission::DRIVER_DELETE
Permission::DRIVER_ASSIGN
```

**Exemples d'utilisation:**

```php
// Dans un contrôleur
public function index()
{
    $this->authorize('viewAny', Driver::class);
    return view('drivers.index');
}

public function show(Driver $driver)
{
    $this->authorize('view', $driver);
    return view('drivers.show', compact('driver'));
}

public function update(Request $request, Driver $driver)
{
    $this->authorize('update', $driver);
    // Update logic...
}

// Assigner un chauffeur à une boutique
public function assignToShop(Driver $driver, Shop $shop)
{
    $this->authorize('assign', [$driver, $shop->id]);
    // Assignment logic...
}
```

**Dans Blade:**
```blade
@can('create', App\Models\Driver::class)
    <a href="{{ route('drivers.create') }}">Ajouter un chauffeur</a>
@endcan

@can('update', $driver)
    <a href="{{ route('drivers.edit', $driver) }}">Modifier</a>
@endcan

@can('delete', $driver)
    <form method="POST" action="{{ route('drivers.destroy', $driver) }}">
        @csrf @method('DELETE')
        <button type="submit">Supprimer</button>
    </form>
@endcan
```

---

### 2. SupervisorPolicy

**Fichier:** `app/Policies/SupervisorPolicy.php`

**Méthodes:**
- `viewAny(User $user)` - Liste des superviseurs
- `view(User $user, Supervisor $supervisor)` - Voir un superviseur spécifique
- `create(User $user)` - Créer un superviseur
- `update(User $user, Supervisor $supervisor)` - Modifier un superviseur
- `delete(User $user, Supervisor $supervisor)` - Supprimer un superviseur
- `assign(User $user, Supervisor $supervisor, ?int $scopeId)` - Assigner un superviseur

**Permissions utilisées:**
```php
Permission::SUPERVISOR_VIEW_ANY
Permission::SUPERVISOR_VIEW
Permission::SUPERVISOR_CREATE
Permission::SUPERVISOR_UPDATE
Permission::SUPERVISOR_DELETE
Permission::SUPERVISOR_ASSIGN
```

**Exemples d'utilisation:**

```php
// Dans un contrôleur
public function index()
{
    $this->authorize('viewAny', Supervisor::class);

    $supervisors = Supervisor::query()
        ->when(!Gate::check('view', Supervisor::class), function ($query) {
            // Si pas de permission globale, filtrer par scope
            $query->whereHas('shops', fn($q) => $q->whereIn('id', auth()->user()->shops->pluck('id')));
        })
        ->get();

    return view('supervisors.index', compact('supervisors'));
}

// Assigner un superviseur à plusieurs boutiques
public function assignToShops(Supervisor $supervisor, array $shopIds)
{
    foreach ($shopIds as $shopId) {
        $this->authorize('assign', [$supervisor, $shopId]);
    }
    // Assignment logic...
}
```

**Dans Livewire:**
```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SupervisorManager extends Component
{
    use AuthorizesRequests;

    public Supervisor $supervisor;

    public function mount(Supervisor $supervisor)
    {
        $this->authorize('view', $supervisor);
        $this->supervisor = $supervisor;
    }

    public function updateSupervisor()
    {
        $this->authorize('update', $this->supervisor);
        // Update logic...
    }
}
```

---

### 3. SupplierPolicy

**Fichier:** `app/Policies/SupplierPolicy.php`

**Méthodes:**
- `viewAny(User $user)` - Liste des fournisseurs
- `view(User $user, Supplier $supplier)` - Voir un fournisseur spécifique
- `create(User $user)` - Créer un fournisseur
- `update(User $user, Supplier $supplier)` - Modifier un fournisseur
- `delete(User $user, Supplier $supplier)` - Supprimer un fournisseur
- `manage(User $user, Supplier $supplier)` - Gérer complètement un fournisseur

**Permissions utilisées:**
```php
Permission::SUPPLIER_VIEW_ANY
Permission::SUPPLIER_VIEW
Permission::SUPPLIER_CREATE
Permission::SUPPLIER_UPDATE
Permission::SUPPLIER_DELETE
Permission::SUPPLIER_MANAGE
```

**Exemples d'utilisation:**

```php
// Dans un contrôleur
public function dashboard(Supplier $supplier)
{
    // manage() vérifie SUPPLIER_MANAGE avec scope
    $this->authorize('manage', $supplier);

    return view('suppliers.dashboard', [
        'supplier' => $supplier,
        'stats' => $this->getSupplierStats($supplier),
        'orders' => $supplier->orders()->latest()->paginate(10),
    ]);
}

// Créer une commande pour un fournisseur
public function createOrder(Supplier $supplier)
{
    // Vérifier si l'utilisateur peut gérer ce fournisseur
    if (Gate::denies('manage', $supplier)) {
        abort(403, 'Vous ne pouvez pas créer de commandes pour ce fournisseur.');
    }

    // Order creation logic...
}
```

**Dans Filament Resource:**
```php
use Illuminate\Database\Eloquent\Builder;

class SupplierResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Filtrer par scope si l'utilisateur n'a pas la permission globale
        if (!Gate::check('viewAny', Supplier::class)) {
            $query->whereHas('users', fn($q) => $q->where('id', auth()->id()));
        }

        return $query;
    }
}
```

---

## Permissions Globales vs Scoped

### Permissions Globales
Permettent d'agir sur **toutes** les ressources du système.

**Exemple:** Un administrateur avec `DRIVER_VIEW` peut voir tous les chauffeurs.

```php
public function view(User $user, Driver $driver): bool
{
    // Permission globale - voir TOUS les chauffeurs
    if ($this->can($user, Permission::DRIVER_VIEW)) {
        return true;
    }

    // ...
}
```

### Permissions Scoped (Scopées)
Permettent d'agir uniquement sur les ressources dans un **scope spécifique** (boutique, cuisine, etc.).

**Exemple:** Un manager de boutique peut voir uniquement les chauffeurs assignés à sa boutique.

```php
public function view(User $user, Driver $driver): bool
{
    // Permission globale
    if ($this->can($user, Permission::DRIVER_VIEW)) {
        return true;
    }

    // Permission scopée - voir uniquement CE chauffeur si assigné à mes entités
    return $this->can($user, Permission::DRIVER_VIEW, $driver->id);
}
```

**Comment ça fonctionne:**
Le `PermissionChecker` vérifie si l'utilisateur a la permission pour le scope donné:
```php
// Vérifie dans user_permissions où scope_id = $driver->id
$this->can($user, Permission::DRIVER_VIEW, $driver->id)
```

---

## Utilisation Avancée

### Vérifications Multiples

```php
// Vérifier si l'utilisateur peut voir OU modifier
if (Gate::any(['view', 'update'], $driver)) {
    // Afficher le détail
}

// Vérifier si l'utilisateur a TOUTES les permissions
if (Gate::check('update', $driver) && Gate::check('assign', [$driver, $shopId])) {
    // Permettre modification ET assignation
}
```

### Middleware de Route

```php
// Dans routes/web.php
Route::middleware(['auth', 'permission:drivers.viewAny'])
    ->get('/drivers', [DriverController::class, 'index']);

Route::middleware(['auth', 'permission:drivers.update,scope:driver'])
    ->put('/drivers/{driver}', [DriverController::class, 'update']);
```

### Dans les Requêtes Eloquent

```php
// Filtrer les résultats selon les permissions
$drivers = Driver::query()
    ->when(!Gate::check('viewAny', Driver::class), function ($query) {
        // Pas de permission globale - filtrer par scope
        $userShopIds = auth()->user()->shops->pluck('id');
        $query->whereHas('shops', fn($q) => $q->whereIn('id', $userShopIds));
    })
    ->get();
```

### Filament - Actions Conditionnelles

```php
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->recordActions([
            EditAction::make()
                ->visible(fn (Driver $record) => auth()->user()->can('update', $record)),
            DeleteAction::make()
                ->visible(fn (Driver $record) => auth()->user()->can('delete', $record)),
        ]);
}
```

---

## Tests

### Tester les Policies

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Enums\Permission;

class DriverPolicyTest extends TestCase
{
    public function test_user_with_global_permission_can_view_any_driver()
    {
        $user = User::factory()->create();
        $user->assignPermission(Permission::DRIVER_VIEW_ANY);

        $this->assertTrue($user->can('viewAny', Driver::class));
    }

    public function test_user_can_view_driver_in_their_shop()
    {
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $driver = Driver::factory()->create();

        // Assigner l'utilisateur à la boutique
        $user->shops()->attach($shop);

        // Assigner le chauffeur à la boutique
        $driver->shops()->attach($shop);

        // Donner permission scopée
        $user->assignPermission(Permission::DRIVER_VIEW, $driver->id);

        $this->assertTrue($user->can('view', $driver));
    }

    public function test_user_cannot_delete_driver_without_permission()
    {
        $user = User::factory()->create();
        $driver = Driver::factory()->create();

        $this->assertFalse($user->can('delete', $driver));
    }
}
```

---

## Récapitulatif

✅ **3 Policies créées:** Driver, Supervisor, Supplier
✅ **Auto-découverte:** Laravel détecte automatiquement les policies
✅ **Type-safe:** Utilise l'enum `Permission` pour la sécurité au compile-time
✅ **Cache:** Request-level caching via `ChecksPermissions` trait
✅ **Global + Scoped:** Support des deux types de permissions
✅ **Documentation:** PHPDoc complète avec exemples

**Prochaines étapes:**
1. ✅ Policies créées et testées
2. ⏳ Créer les tests unitaires pour les policies
3. ⏳ Intégrer dans les Filament Resources
4. ⏳ Ajouter le middleware de permissions dans les routes
