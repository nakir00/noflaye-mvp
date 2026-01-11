# Quick Reference - Architecture Multi-Panels

## 📁 Structure du Projet

```
app/Filament/
├── BaseResource.php
├── Admin/              # 📱 Panel Admin (Rouge)
│   ├── Clusters/
│   │   ├── Business/           🏢 Entités métier
│   │   ├── Permissions/        🔐 Système permissions
│   │   └── AccessControl/      👥 Utilisateurs & groupes
│   ├── Pages/
│   └── Widgets/
│
├── Shop/               # 🏪 Panel Boutique (Bleu)
│   ├── ShopPanelProvider.php
│   └── Clusters/
│       ├── Orders/             🛒 Commandes
│       ├── Inventory/          📦 Inventaire
│       ├── Permissions/        🔐 Permissions
│       └── MyAccount/          👤 Mon compte
│
├── Kitchen/            # 👨‍🍳 Panel Cuisine (Orange)
│   ├── KitchenPanelProvider.php
│   └── Clusters/
│       ├── Production/         🍳 Production
│       ├── Permissions/        🔐 Permissions
│       └── MyAccount/          👤 Mon compte
│
├── Delivery/           # 🚚 Panel Livraison (Vert)
│   ├── DeliveryPanelProvider.php
│   └── Clusters/
│       ├── Routes/             🗺️ Tournées
│       ├── Permissions/        🔐 Permissions
│       └── MyAccount/          👤 Mon compte
│
└── Supplier/           # 📦 Panel Fournisseur (Violet)
    ├── SupplierPanelProvider.php
    └── Clusters/
        ├── Supply/             📋 Approvisionnement
        ├── Permissions/        🔐 Permissions
        └── MyAccount/          👤 Mon compte
```

## 🎨 Panels Overview

| Panel | ID | Path | Color | Primary Users |
|-------|-----|------|-------|---------------|
| Admin | `admin` | `/admin` | 🔴 Red | Super Admins |
| Shop | `shop` | `/shop` | 🔵 Blue | Shop Managers |
| Kitchen | `kitchen` | `/kitchen` | 🟠 Orange | Kitchen Staff |
| Delivery | `delivery` | `/delivery` | 🟢 Green | Drivers |
| Supplier | `supplier` | `/supplier` | 🟣 Purple | Suppliers |

## ⚡ Commandes Essentielles

### Créer une Ressource

```bash
# Template général
php artisan make:filament-resource {Model} \
  --panel={panel-id} \
  --cluster={ClusterPath} \
  --generate

# Exemples
php artisan make:filament-resource Shop --panel=admin --cluster=Business/BusinessCluster --generate
php artisan make:filament-resource Order --panel=shop --cluster=Orders/OrdersCluster --generate
```

### Créer un Cluster

```bash
php artisan make:filament-cluster {Name} --panel={panel-id}

# Exemple
php artisan make:filament-cluster Production --panel=kitchen
```

### Créer une Page

```bash
php artisan make:filament-page {Name} --panel={panel-id} --cluster={ClusterPath}

# Exemple
php artisan make:filament-page Analytics --panel=admin --cluster=Permissions/PermissionsCluster
```

### Créer un Widget

```bash
php artisan make:filament-widget {Name} --panel={panel-id} --stats-overview

# Exemple
php artisan make:filament-widget OrderStats --panel=shop --stats-overview
```

## 🔧 Configuration

### Enregistrer un Panel

**bootstrap/providers.php**:
```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Filament\Shop\ShopPanelProvider::class,
    // ... autres panels
];
```

### Découverte des Clusters

**Dans le PanelProvider**:
```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->discoverClusters(
            in: app_path('Filament/Admin/Clusters'),
            for: 'App\\Filament\\Admin\\Clusters'
        );
}
```

## 📝 Code Snippets

### Ressource avec Cluster

```php
<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Shops;

use App\Filament\Admin\Clusters\Business\BusinessCluster;
use App\Models\Shop;
use Filament\Resources\Resource;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;
    protected static ?string $cluster = BusinessCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    // ... form(), table(), getPages()
}
```

### Cluster avec Badge

```php
<?php

namespace App\Filament\Admin\Clusters\Permissions;

use App\Models\PermissionRequest;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class PermissionsCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationBadge(): ?string
    {
        $count = PermissionRequest::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = PermissionRequest::where('status', 'pending')->count();
        return $count > 10 ? 'danger' : ($count > 5 ? 'warning' : 'success');
    }
}
```

### Policy par Panel

```php
public function viewAny(User $user): bool
{
    $panelId = filament()->getCurrentPanel()->getId();

    return match ($panelId) {
        'admin' => $user->hasPermission('admin.shops.viewAny'),
        'shop' => $user->hasPermission('shop.shops.viewAny'),
        default => false,
    };
}
```

### Navigation Groups

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->navigationGroups([
            'Business Management',
            'Permissions & Security',
            'Access Control',
        ]);
}
```

## 🎯 URLs Patterns

```
# Admin Panel
/admin/business/shops
/admin/business/shops/create
/admin/business/shops/{id}/edit
/admin/permissions/templates
/admin/access-control/users

# Shop Panel
/shop/orders
/shop/orders/create
/shop/inventory/products

# Kitchen Panel
/kitchen/production/recipes

# Delivery Panel
/delivery/routes

# Supplier Panel
/supplier/supply/purchase-orders
```

## 🔐 Permissions Naming Convention

```
{panel}.{cluster}.{resource}.{action}

Exemples:
admin.business.shops.viewAny
admin.business.shops.create
admin.permissions.templates.edit
shop.orders.orders.viewAny
kitchen.production.recipes.create
```

## 🎨 Icons & Colors

### Clusters Icons

```php
'heroicon-o-building-storefront'  // Business
'heroicon-o-shield-check'         // Permissions
'heroicon-o-users'                // Access Control
'heroicon-o-shopping-cart'        // Orders
'heroicon-o-cube'                 // Inventory
'heroicon-o-beaker'               // Production
'heroicon-o-truck'                // Routes/Delivery
'heroicon-o-clipboard-document-list' // Supply
'heroicon-o-user-circle'          // My Account
```

### Panel Colors

```php
Color::Red      // Admin
Color::Blue     // Shop
Color::Orange   // Kitchen
Color::Green    // Delivery
Color::Purple   // Supplier
```

## 🧪 Testing

```php
use function Pest\Livewire\livewire;

it('can list shops in admin panel', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    livewire(ListShops::class)
        ->assertSuccessful();
});

it('can access shop panel', function () {
    $manager = User::factory()->shopManager()->create();
    $this->actingAs($manager);

    $response = $this->get('/shop');
    $response->assertSuccessful();
});
```

## 🔄 Migration Checklist

- [ ] Créer la structure des dossiers panels
- [ ] Créer les PanelProviders
- [ ] Créer les clusters
- [ ] Enregistrer les panels dans `bootstrap/providers.php`
- [ ] Migrer les ressources existantes
- [ ] Mettre à jour les namespaces
- [ ] Ajouter la propriété `$cluster`
- [ ] Mettre à jour les imports dans les Pages
- [ ] Tester chaque panel
- [ ] Mettre à jour les Policies
- [ ] Adapter les tests
- [ ] Supprimer l'ancienne structure

## 🆘 Debug

```bash
# Clear cache
php artisan optimize:clear

# List routes
php artisan route:list --name=filament

# Check autoload
composer dump-autoload

# Format code
vendor/bin/pint

# Run tests
php artisan test
```

## 📊 Metrics & Monitoring

```php
// Get current panel
$panel = filament()->getCurrentPanel();
$panelId = $panel->getId();

// Get panel color
$color = $panel->getColors()['primary'];

// Check if in specific panel
if (filament()->getCurrentPanel()->getId() === 'admin') {
    // Admin-specific logic
}
```

## 🎓 Best Practices

### ✅ DO
- Use consistent naming conventions
- Define `$cluster` in all resources
- Create panel-specific policies
- Test each panel separately
- Document panel-specific features

### ❌ DON'T
- Mix resources from different panels
- Forget to update namespaces after migration
- Skip permission checks per panel
- Create dependencies between panels
- Ignore the cluster structure

## 📚 Documentation Links

- [Migration Guide](./MIGRATION_GUIDE.md)
- [Architecture Details](./ARCHITECTURE_MULTI_PANELS.md)
- [Getting Started](./GETTING_STARTED_PANELS.md)
- [Filament Docs](https://filamentphp.com/docs/4.x)
