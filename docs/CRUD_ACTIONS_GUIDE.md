# CRUD Actions Guide - NoFlaye MVP

## Overview

Les Actions CRUD dans NoFlaye MVP utilisent le pattern Action de Lorisleiva pour encapsuler la logique métier réutilisable. Chaque action gère une opération spécifique (Create, Update, Delete) avec validation, audit logging, cache management, et métriques de performance.

## Actions Créées

### Entités avec CRUD complet

#### 1. **Driver** (Chauffeur)
- ✅ `CreateDriver` - Créer un chauffeur
- ✅ `UpdateDriver` - Modifier un chauffeur
- ✅ `DeleteDriver` - Supprimer un chauffeur

#### 2. **Supervisor** (Superviseur)
- ✅ `CreateSupervisor` - Créer un superviseur
- ✅ `UpdateSupervisor` - Modifier un superviseur
- ✅ `DeleteSupervisor` - Supprimer un superviseur

#### 3. **Supplier** (Fournisseur)
- ✅ `CreateSupplier` - Créer un fournisseur
- ✅ `UpdateSupplier` - Modifier un fournisseur
- ✅ `DeleteSupplier` - Supprimer un fournisseur

#### 4. **Shop** (Boutique)
- ✅ `CreateShop` - Créer une boutique
- ✅ `UpdateShop` - Modifier une boutique
- ✅ `DeleteShop` - Supprimer une boutique

#### 5. **Kitchen** (Cuisine)
- ✅ `CreateKitchen` - Créer une cuisine
- ✅ `UpdateKitchen` - Modifier une cuisine
- ✅ `DeleteKitchen` - Supprimer une cuisine

**Total: 15 actions CRUD** (5 entités × 3 opérations)

## Architecture Standard

Toutes les actions suivent la même architecture:

```php
<?php

namespace App\Actions\{Entity};

use App\Data\{Entity}\{Entity}Data;
use App\Models\{Entity};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class Create{Entity}
{
    use AsAction;

    public function handle({Entity}Data $data): {Entity}
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $startTime) {
                // 1. Create entity
                $entity = {Entity}::create([...]);

                // 2. Log activity
                activity()->performedOn($entity)
                    ->causedBy(Auth::user())
                    ->withProperties([...])
                    ->log('{entity}_created');

                // 3. Invalidate caches
                Cache::tags(['{entities}'])->flush();

                // 4. Log metrics
                $this->logMetrics($startTime, 'success', $entity);

                return $entity;
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', null, $e->getMessage());
            throw $e;
        }
    }

    protected function logMetrics(...) { }
    public function asJob(...) { }
}
```

## Fonctionnalités Communes

### 1. **Validation via DTOs**

Toutes les actions utilisent des DTOs Spatie Laravel Data:

```php
$data = DriverData::from([
    'name' => 'John Doe',
    'slug' => 'john-doe',
    'email' => 'john@example.com',
    'phone' => '+221771234567',
    'vehicle_type' => 'Van',
    'vehicle_number' => 'DK-1234-AB',
    'license_number' => 'B123456',
    'is_active' => true,
    'is_available' => true,
]);

$driver = CreateDriver::run($data);
```

### 2. **Activity Logging**

Utilise Spatie Activity Log pour l'audit trail:

```php
activity()
    ->performedOn($driver)
    ->causedBy(Auth::user())
    ->withProperties([
        'name' => $driver->name,
        'vehicle_type' => $driver->vehicle_type,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ])
    ->log('driver_created');
```

### 3. **Cache Invalidation**

Utilise des tags Redis pour une invalidation granulaire:

```php
// Create/Update
Cache::tags(['drivers'])->flush();

// Update/Delete
Cache::tags(['drivers', "driver.{$driver->id}"])->flush();
```

### 4. **Performance Metrics**

Enregistre les métriques de performance dans les logs:

```php
Log::info('Driver creation', [
    'action' => 'create_driver',
    'outcome' => 'success',
    'duration_ms' => 45.23,
    'driver_id' => 123,
    'user_id' => 1,
    'ip_address' => '192.168.1.1',
]);
```

### 5. **Transaction Safety**

Toutes les opérations sont encapsulées dans des transactions DB:

```php
DB::transaction(function () use ($data) {
    // All operations are atomic
    $entity = Entity::create([...]);
    activity()->log('created');
    Cache::flush();
    return $entity;
});
```

### 6. **Asynchronous Execution**

Support natif pour l'exécution en queue:

```php
CreateDriver::dispatch($data); // Dispatch to queue
CreateDriver::dispatchSync($data); // Synchronous
```

---

## Exemples d'Utilisation

### Create Actions

#### Exemple 1: Créer un Driver

```php
use App\Actions\Drivers\CreateDriver;
use App\Data\Drivers\DriverData;

// Dans un contrôleur
public function store(Request $request)
{
    $data = DriverData::from($request->validated());

    $driver = CreateDriver::run($data);

    return redirect()
        ->route('drivers.show', $driver)
        ->with('success', 'Driver created successfully');
}
```

#### Exemple 2: Créer un Supplier

```php
use App\Actions\Suppliers\CreateSupplier;
use App\Data\Suppliers\SupplierData;

$data = SupplierData::from([
    'name' => 'ABC Supplies',
    'slug' => 'abc-supplies',
    'email' => 'contact@abc.com',
    'phone' => '+221771234567',
    'address' => '123 Business St',
    'is_active' => true,
]);

$supplier = CreateSupplier::run($data);
```

---

### Update Actions

#### Exemple 1: Modifier un Shop

```php
use App\Actions\Shops\UpdateShop;
use App\Data\Shops\ShopData;

public function update(Request $request, Shop $shop)
{
    $data = ShopData::from($request->validated());

    $updated = UpdateShop::run($shop, $data);

    return back()->with('success', 'Shop updated successfully');
}
```

#### Exemple 2: Change Tracking

Les Update actions suivent automatiquement les changements:

```php
$kitchen = Kitchen::find(1);
$data = KitchenData::from([
    'name' => 'Updated Name', // Changed
    'slug' => 'same-slug',    // Unchanged
    'is_active' => false,     // Changed
]);

$updated = UpdateKitchen::run($kitchen, $data);

// Activity log contiendra:
// changes: ['name' => 'Updated Name', 'is_active' => false]
// old: ['name' => 'Old Name', 'is_active' => true]
```

---

### Delete Actions

#### Exemple 1: Supprimer un Supervisor

```php
use App\Actions\Supervisors\DeleteSupervisor;

public function destroy(Supervisor $supervisor)
{
    DeleteSupervisor::run($supervisor);

    return redirect()
        ->route('supervisors.index')
        ->with('success', 'Supervisor deleted successfully');
}
```

#### Exemple 2: Force Delete

```php
// Soft delete (default)
DeleteDriver::run($driver);

// Force delete (permanent)
DeleteDriver::run($driver, force: true);
```

---

## Utilisation dans Filament

### Resource Create Page

```php
use App\Actions\Drivers\CreateDriver;
use App\Data\Drivers\DriverData;

class CreateDriver extends CreateRecord
{
    protected static string $resource = DriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $driverData = DriverData::from($data);

        return \App\Actions\Drivers\CreateDriver::run($driverData);
    }
}
```

### Resource Edit Page

```php
use App\Actions\Suppliers\UpdateSupplier;
use App\Data\Suppliers\SupplierData;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $supplierData = SupplierData::from($data);

        return \App\Actions\Suppliers\UpdateSupplier::run($record, $supplierData);
    }
}
```

---

## Utilisation avec Jobs/Queues

### Dispatch to Queue

```php
use App\Actions\Shops\CreateShop;
use App\Data\Shops\ShopData;

// Async execution
$data = ShopData::from([...]);
CreateShop::dispatch($data);

// Sync execution
CreateShop::dispatchSync($data);

// With job options
CreateShop::dispatch($data)
    ->onQueue('high-priority')
    ->delay(now()->addMinutes(5));
```

### Dans un Job

```php
use App\Actions\Kitchens\UpdateKitchen;
use App\Data\Kitchens\KitchenData;

class ProcessKitchenUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Kitchen $kitchen,
        public array $updates
    ) {}

    public function handle(): void
    {
        $data = KitchenData::from($this->updates);
        UpdateKitchen::run($this->kitchen, $data);
    }
}
```

---

## Tests

### Tester les Create Actions

```php
use Tests\TestCase;
use App\Actions\Drivers\CreateDriver;
use App\Data\Drivers\DriverData;
use App\Models\Driver;

class CreateDriverTest extends TestCase
{
    public function test_creates_driver_with_valid_data()
    {
        $data = DriverData::from([
            'name' => 'Test Driver',
            'slug' => 'test-driver',
            'email' => 'test@example.com',
            'phone' => '+221771234567',
            'vehicle_type' => 'Van',
            'vehicle_number' => 'DK-1234-AB',
            'license_number' => 'B123456',
            'is_active' => true,
            'is_available' => true,
        ]);

        $driver = CreateDriver::run($data);

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertEquals('Test Driver', $driver->name);
        $this->assertDatabaseHas('drivers', [
            'name' => 'Test Driver',
            'slug' => 'test-driver',
        ]);
    }

    public function test_logs_activity_on_creation()
    {
        $data = DriverData::from([...]);

        $driver = CreateDriver::run($data);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Driver::class,
            'subject_id' => $driver->id,
            'description' => 'driver_created',
        ]);
    }

    public function test_invalidates_cache_on_creation()
    {
        Cache::shouldReceive('tags')
            ->with(['drivers'])
            ->once()
            ->andReturnSelf();

        Cache::shouldReceive('flush')
            ->once();

        $data = DriverData::from([...]);
        CreateDriver::run($data);
    }
}
```

### Tester les Update Actions

```php
use App\Actions\Shops\UpdateShop;
use App\Data\Shops\ShopData;

class UpdateShopTest extends TestCase
{
    public function test_updates_shop_with_changes()
    {
        $shop = Shop::factory()->create(['name' => 'Old Name']);

        $data = ShopData::from([
            'name' => 'New Name',
            'slug' => $shop->slug,
            'is_active' => true,
        ]);

        $updated = UpdateShop::run($shop, $data);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'name' => 'New Name',
        ]);
    }

    public function test_tracks_changes_in_activity_log()
    {
        $shop = Shop::factory()->create(['name' => 'Old Name']);
        $data = ShopData::from(['name' => 'New Name', ...]);

        UpdateShop::run($shop, $data);

        $activity = $shop->activities()->first();
        $this->assertEquals('shop_updated', $activity->description);
        $this->assertArrayHasKey('changes', $activity->properties);
        $this->assertEquals('New Name', $activity->properties['changes']['name']);
    }
}
```

### Tester les Delete Actions

```php
use App\Actions\Supervisors\DeleteSupervisor;

class DeleteSupervisorTest extends TestCase
{
    public function test_soft_deletes_supervisor()
    {
        $supervisor = Supervisor::factory()->create();

        $deleted = DeleteSupervisor::run($supervisor);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('supervisors', ['id' => $supervisor->id]);
    }

    public function test_force_deletes_supervisor()
    {
        $supervisor = Supervisor::factory()->create();

        DeleteSupervisor::run($supervisor, force: true);

        $this->assertDatabaseMissing('supervisors', ['id' => $supervisor->id]);
    }

    public function test_logs_deletion_activity()
    {
        $supervisor = Supervisor::factory()->create();

        DeleteSupervisor::run($supervisor);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Supervisor::class,
            'subject_id' => $supervisor->id,
            'description' => 'supervisor_deleted',
        ]);
    }
}
```

---

## Métriques de Performance

### Visualiser les Métriques

Les actions enregistrent des métriques dans les logs Laravel:

```php
// storage/logs/laravel.log
[2026-01-04 10:30:45] local.INFO: Driver creation {"action":"create_driver","outcome":"success","duration_ms":45.23,"driver_id":123,"user_id":1,"ip_address":"192.168.1.1"}
```

### Analyser les Performances

```bash
# Moyenne des temps de création de drivers
grep "create_driver" storage/logs/laravel.log | grep "success" | \
  awk -F'"duration_ms":' '{print $2}' | \
  awk -F',' '{sum+=$1; count++} END {print "Average:", sum/count, "ms"}'

# Nombre d'échecs
grep "create_driver" storage/logs/laravel.log | grep "failed" | wc -l
```

---

## Bonnes Pratiques

### 1. Toujours Utiliser les Actions

❌ **Éviter:**
```php
public function store(Request $request)
{
    $driver = Driver::create($request->all()); // Pas de validation, audit, cache
    return redirect()->route('drivers.index');
}
```

✅ **Préférer:**
```php
public function store(Request $request)
{
    $data = DriverData::from($request->validated());
    $driver = CreateDriver::run($data); // Validation, audit, cache inclus
    return redirect()->route('drivers.index');
}
```

### 2. Utiliser les DTOs

❌ **Éviter:**
```php
UpdateShop::run($shop, $request->all()); // Pas de validation
```

✅ **Préférer:**
```php
$data = ShopData::from($request->validated());
UpdateShop::run($shop, $data); // Validé et type-safe
```

### 3. Gérer les Exceptions

```php
try {
    $supplier = CreateSupplier::run($data);
    return response()->json($supplier, 201);
} catch (\Illuminate\Validation\ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
} catch (\Exception $e) {
    Log::error('Supplier creation failed', ['error' => $e->getMessage()]);
    return response()->json(['message' => 'Server error'], 500);
}
```

### 4. Async pour Opérations Lentes

```php
// Synchrone pour petites opérations
$driver = CreateDriver::run($data);

// Async pour opérations lourdes
CreateDriver::dispatch($data)->onQueue('long-running');
```

---

## Récapitulatif

✅ **15 Actions CRUD créées**
- 5 entités (Driver, Supervisor, Supplier, Shop, Kitchen)
- 3 opérations chacune (Create, Update, Delete)

✅ **Fonctionnalités:**
- Validation via DTOs Spatie
- Activity logging avec Spatie Activitylog
- Cache invalidation avec tags Redis
- Performance metrics dans les logs
- Transaction safety
- Async/Queue support
- Change tracking (Update)
- Force delete support (Delete)

✅ **Documentation complète:**
- Architecture standard
- Exemples d'utilisation
- Intégration Filament
- Tests unitaires
- Métriques de performance

**Prochaines étapes:**
1. Créer des tests pour chaque action
2. Intégrer dans les Filament Resources
3. Configurer les queues pour async
4. Monitorer les métriques de performance
