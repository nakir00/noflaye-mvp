# Guide de Démarrage - Architecture Multi-Panels

## 🚀 Quick Start

### 1. Enregistrer les Panels

Éditez `bootstrap/providers.php` et ajoutez tous les PanelProviders:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,

    // Filament Panels
    App\Providers\Filament\AdminPanelProvider::class,
    App\Filament\Shop\ShopPanelProvider::class,
    App\Filament\Kitchen\KitchenPanelProvider::class,
    App\Filament\Delivery\DeliveryPanelProvider::class,
    App\Filament\Supplier\SupplierPanelProvider::class,
];
```

### 2. Vérifier la Configuration

```bash
# Effacer le cache
php artisan optimize:clear

# Lister les routes Filament
php artisan route:list --name=filament

# Vous devriez voir:
# - filament.admin.*
# - filament.shop.*
# - filament.kitchen.*
# - filament.delivery.*
# - filament.supplier.*
```

### 3. Accéder aux Panels

Lancez le serveur:
```bash
php artisan serve
```

Accédez aux différents panels:
- Admin: http://localhost:8000/admin
- Shop: http://localhost:8000/shop
- Kitchen: http://localhost:8000/kitchen
- Delivery: http://localhost:8000/delivery
- Supplier: http://localhost:8000/supplier

## 📝 Créer des Ressources dans les Clusters

### Syntaxe Générale

```bash
php artisan make:filament-resource {ModelName} \
  --panel={panelId} \
  --cluster={ClusterNamespace} \
  --generate
```

### Exemples Pratiques

#### 1. Créer ShopResource dans Business Cluster (Admin Panel)

```bash
php artisan make:filament-resource Shop \
  --panel=admin \
  --cluster=Business/BusinessCluster \
  --generate
```

Cela créera:
```
app/Filament/Admin/Clusters/Business/Resources/Shops/
├── ShopResource.php
├── Pages/
│   ├── CreateShop.php
│   ├── EditShop.php
│   └── ListShops.php
├── Schemas/
│   └── ShopForm.php
└── Tables/
    └── ShopsTable.php
```

#### 2. Créer PermissionTemplateResource dans Permissions Cluster

```bash
php artisan make:filament-resource PermissionTemplate \
  --panel=admin \
  --cluster=Permissions/PermissionsCluster \
  --generate
```

#### 3. Créer UserResource dans Access Control Cluster

```bash
php artisan make:filament-resource User \
  --panel=admin \
  --cluster=AccessControl/AccessControlCluster \
  --generate \
  --view
```

L'option `--view` ajoute une page View en plus de Create et Edit.

#### 4. Créer OrderResource dans Shop Panel

```bash
php artisan make:filament-resource Order \
  --panel=shop \
  --cluster=Orders/OrdersCluster \
  --generate
```

## 🔧 Structure d'une Ressource dans un Cluster

Après création, votre ressource contiendra:

```php
<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Shops;

use App\Filament\Admin\Clusters\Business\BusinessCluster;
use App\Models\Shop;
use Filament\Resources\Resource;
// ...

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    // Lien vers le cluster
    protected static ?string $cluster = BusinessCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    // Le reste de votre configuration...
}
```

## 📄 Créer des Pages Custom dans un Cluster

### Page Simple

```bash
php artisan make:filament-page MyPermissions \
  --panel=admin \
  --cluster=Permissions/PermissionsCluster
```

### Page dans une Ressource

```bash
php artisan make:filament-page ManageShopSettings \
  --panel=admin \
  --resource=Shops/ShopResource \
  --type=custom
```

## 🎨 Créer des Widgets dans un Cluster

```bash
php artisan make:filament-widget PermissionStatsOverview \
  --panel=admin \
  --cluster=Permissions/PermissionsCluster \
  --stats-overview
```

Types de widgets disponibles:
- `--stats-overview`: Vue d'ensemble statistiques
- `--chart`: Graphique
- `--table`: Table widget

## 🔐 Configurer les Permissions par Panel

### Dans chaque PanelProvider

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('shop')
        // ...
        ->authMiddleware([
            Authenticate::class,
        ])
        // Ajouter des guards spécifiques si nécessaire
        ->authGuard('web');
}
```

### Dans vos Policies

```php
// app/Policies/ShopPolicy.php

public function viewAny(User $user): bool
{
    // Vérifier si l'utilisateur peut voir dans le panel admin
    if (filament()->getCurrentPanel()->getId() === 'admin') {
        return $user->hasPermission('admin.shops.viewAny');
    }

    // Vérifier si l'utilisateur peut voir dans le panel shop
    if (filament()->getCurrentPanel()->getId() === 'shop') {
        return $user->hasPermission('shop.shops.viewAny');
    }

    return false;
}
```

## 🎯 Personnaliser la Navigation

### Dans le Cluster

```php
// app/Filament/Admin/Clusters/Business/BusinessCluster.php

class BusinessCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Business';
    protected static ?int $navigationSort = 1;

    // Position de la sous-navigation
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    // Badge dynamique
    public static function getNavigationBadge(): ?string
    {
        return Shop::count();
    }
}
```

### Dans la Ressource

```php
class ShopResource extends Resource
{
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Entities';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}
```

## 🔄 Migration Progressive

Si vous avez déjà des ressources dans l'ancienne structure:

### Étape 1: Copier (ne pas déplacer)

```bash
# Copier une ressource existante vers le nouveau cluster
cp -r app/Filament/Resources/ShopResource \
  app/Filament/Admin/Clusters/Business/Resources/Shops/
```

### Étape 2: Mettre à jour le Namespace

Dans `ShopResource.php`:

```php
<?php

// Ancien
namespace App\Filament\Resources;

// Nouveau
namespace App\Filament\Admin\Clusters\Business\Resources\Shops;
```

### Étape 3: Ajouter la propriété $cluster

```php
use App\Filament\Admin\Clusters\Business\BusinessCluster;

class ShopResource extends Resource
{
    protected static ?string $cluster = BusinessCluster::class;

    // ...
}
```

### Étape 4: Mettre à jour les imports dans les Pages

Dans `CreateShop.php`, `EditShop.php`, etc:

```php
<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Shops\Pages;

use App\Filament\Admin\Clusters\Business\Resources\Shops\ShopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShop extends CreateRecord
{
    protected static string $resource = ShopResource::class;
}
```

### Étape 5: Vérifier et Tester

```bash
# Vérifier les routes
php artisan route:list --name=filament.admin.clusters.business

# Effacer le cache
php artisan optimize:clear

# Visiter l'interface admin
```

### Étape 6: Supprimer l'ancienne structure

Une fois que tout fonctionne:

```bash
rm -rf app/Filament/Resources/ShopResource
```

## 🧪 Tests

### Tester un Panel Spécifique

```php
// tests/Feature/Admin/ShopResourceTest.php

use App\Models\User;
use function Pest\Livewire\livewire;

it('can list shops in admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(\App\Filament\Admin\Clusters\Business\Resources\Shops\Pages\ListShops::class)
        ->assertSuccessful();
});
```

## 📊 Monitoring & Debug

### Afficher le Panel Actif

```php
// Dans n'importe quel fichier Filament
$currentPanel = filament()->getCurrentPanel();
$panelId = $currentPanel->getId(); // 'admin', 'shop', etc.
```

### Logger les Accès par Panel

```php
// Dans un Middleware ou Observer
Log::info('Access to panel', [
    'panel' => filament()->getCurrentPanel()->getId(),
    'user' => auth()->id(),
    'resource' => request()->route()->getName(),
]);
```

## 🎓 Bonnes Pratiques

### ✅ DO

1. Toujours définir `$cluster` dans vos ressources
2. Utiliser des namespaces cohérents
3. Créer des Policies spécifiques par panel
4. Documenter les spécificités de chaque panel
5. Tester chaque panel séparément

### ❌ DON'T

1. Ne pas mélanger les ressources de différents panels
2. Ne pas oublier de mettre à jour les namespaces après migration
3. Ne pas copier-coller sans adapter les chemins
4. Ne pas négliger les permissions par panel
5. Ne pas créer des dépendances entre panels

## 🆘 Troubleshooting

### Problème: "Class not found"

**Solution**: Vérifiez vos namespaces et imports

```bash
composer dump-autoload
php artisan optimize:clear
```

### Problème: "Cluster not found"

**Solution**: Vérifiez le chemin dans `discoverClusters()`

```php
->discoverClusters(
    in: app_path('Filament/Admin/Clusters'),
    for: 'App\\Filament\\Admin\\Clusters'
)
```

### Problème: Navigation ne s'affiche pas

**Solution**: Vérifiez la propriété `$cluster` et les permissions

```php
protected static ?string $cluster = \App\Filament\Admin\Clusters\Business\BusinessCluster::class;
```

### Problème: Routes en conflit

**Solution**: Assurez-vous que chaque panel a un `id` et `path` unique

```php
->id('admin')
->path('admin')
```

## 📚 Ressources Supplémentaires

- [Documentation Migration](./MIGRATION_GUIDE.md)
- [Architecture Détaillée](./ARCHITECTURE_MULTI_PANELS.md)
- [Filament Clusters Docs](https://filamentphp.com/docs/4.x/panels/navigation/clusters)
