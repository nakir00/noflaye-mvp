# Guide de Migration vers l'Architecture Multi-Panels avec Clusters

## 📁 Structure Créée

La nouvelle architecture multi-panels a été mise en place avec succès :

```
app/Filament/
├── BaseResource.php (partagé)
│
├── Admin/                           📱 PANEL: Administration
│   ├── Clusters/
│   │   ├── Business/               🏢 Gestion Métier
│   │   │   └── BusinessCluster.php
│   │   ├── Permissions/            🔐 Permissions
│   │   │   └── PermissionsCluster.php
│   │   └── AccessControl/          👥 Contrôle d'Accès
│   │       └── AccessControlCluster.php
│   ├── Pages/
│   └── Widgets/
│
├── Shop/                            🏪 PANEL: Gestion Boutique
│   ├── ShopPanelProvider.php
│   ├── Clusters/
│   │   ├── Orders/
│   │   ├── Inventory/
│   │   ├── Permissions/
│   │   └── MyAccount/
│   ├── Pages/
│   └── Widgets/
│
├── Kitchen/                         👨‍🍳 PANEL: Gestion Cuisine
│   ├── KitchenPanelProvider.php
│   └── Clusters/
│       ├── Production/
│       ├── Permissions/
│       └── MyAccount/
│
├── Delivery/                        🚚 PANEL: Gestion Livraison
│   ├── DeliveryPanelProvider.php
│   └── Clusters/
│       ├── Routes/
│       ├── Permissions/
│       └── MyAccount/
│
└── Supplier/                        📦 PANEL: Gestion Fournisseur
    ├── SupplierPanelProvider.php
    └── Clusters/
        ├── Supply/
        ├── Permissions/
        └── MyAccount/
```

## 🎨 Configuration des Panels

### Couleurs des Panels
- **Admin**: Rouge (Red)
- **Shop**: Bleu (Blue)
- **Kitchen**: Orange (Orange)
- **Delivery**: Vert (Green)
- **Supplier**: Violet (Purple)

### URLs d'Accès
- Admin: `/admin`
- Shop: `/shop`
- Kitchen: `/kitchen`
- Delivery: `/delivery`
- Supplier: `/supplier`

## 📝 Prochaines Étapes

### 1. Enregistrer les nouveaux panels dans `bootstrap/providers.php`

Ajoutez les nouveaux PanelProviders :

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Filament\Shop\ShopPanelProvider::class,
    App\Filament\Kitchen\KitchenPanelProvider::class,
    App\Filament\Delivery\DeliveryPanelProvider::class,
    App\Filament\Supplier\SupplierPanelProvider::class,
];
```

### 2. Créer les ressources dans les clusters avec Artisan

#### Pour le Panel Admin - Business Cluster

```bash
# ShopResource
php artisan make:filament-resource Shop --panel=admin --cluster=Business/BusinessCluster --generate

# KitchenResource
php artisan make:filament-resource Kitchen --panel=admin --cluster=Business/BusinessCluster --generate

# SupplierResource (à créer)
php artisan make:filament-resource Supplier --panel=admin --cluster=Business/BusinessCluster --generate --model-namespace=App\\Models

# DriverResource
php artisan make:filament-resource Driver --panel=admin --cluster=Business/BusinessCluster --generate

# SupervisorResource
php artisan make:filament-resource Supervisor --panel=admin --cluster=Business/BusinessCluster --generate
```

#### Pour le Panel Admin - Permissions Cluster

```bash
# PermissionTemplateResource
php artisan make:filament-resource PermissionTemplate --panel=admin --cluster=Permissions/PermissionsCluster --generate

# PermissionWildcardResource
php artisan make:filament-resource PermissionWildcard --panel=admin --cluster=Permissions/PermissionsCluster --generate

# PermissionDelegationResource
php artisan make:filament-resource PermissionDelegation --panel=admin --cluster=Permissions/PermissionsCluster --generate

# PermissionRequestResource
php artisan make:filament-resource PermissionRequest --panel=admin --cluster=Permissions/PermissionsCluster --generate

# PermissionAuditLogResource
php artisan make:filament-resource PermissionAuditLog --panel=admin --cluster=Permissions/PermissionsCluster --generate
```

#### Pour le Panel Admin - Access Control Cluster

```bash
# UserResource
php artisan make:filament-resource User --panel=admin --cluster=AccessControl/AccessControlCluster --generate

# UserGroupResource
php artisan make:filament-resource UserGroup --panel=admin --cluster=AccessControl/AccessControlCluster --generate
```

### 3. Migrer les pages et widgets personnalisés

```bash
# Déplacer les pages de permissions
mv app/Filament/Pages/MyPermissions.php app/Filament/Admin/Clusters/Permissions/Pages/
mv app/Filament/Pages/MyDelegations.php app/Filament/Admin/Clusters/Permissions/Pages/
mv app/Filament/Pages/PermissionAnalyticsDashboard.php app/Filament/Admin/Clusters/Permissions/Pages/

# Déplacer les widgets de permissions
mv app/Filament/Widgets/PermissionStatsWidget.php app/Filament/Admin/Clusters/Permissions/Widgets/
mv app/Filament/Widgets/MostUsedPermissionsWidget.php app/Filament/Admin/Clusters/Permissions/Widgets/
mv app/Filament/Widgets/PermissionGrowthChart.php app/Filament/Admin/Clusters/Permissions/Widgets/
mv app/Filament/Widgets/TemplateAdoptionWidget.php app/Filament/Admin/Clusters/Permissions/Widgets/
```

**Important**: Après avoir déplacé les fichiers, mettez à jour les namespaces :

- Pages: `namespace App\Filament\Admin\Clusters\Permissions\Pages;`
- Widgets: `namespace App\Filament\Admin\Clusters\Permissions\Widgets;`

### 4. Mettre à jour les namespaces et les propriétés $cluster

Dans chaque ressource déplacée, ajoutez :

```php
protected static ?string $cluster = \App\Filament\Admin\Clusters\Business\BusinessCluster::class;
// ou
protected static ?string $cluster = \App\Filament\Admin\Clusters\Permissions\PermissionsCluster::class;
// ou
protected static ?string $cluster = \App\Filament\Admin\Clusters\AccessControl\AccessControlCluster::class;
```

### 5. Créer les Dashboards pour chaque panel

```bash
php artisan make:filament-page ShopDashboard --panel=shop
php artisan make:filament-page KitchenDashboard --panel=kitchen
php artisan make:filament-page DeliveryDashboard --panel=delivery
php artisan make:filament-page SupplierDashboard --panel=supplier
```

### 6. Nettoyer l'ancienne structure (après vérification)

```bash
# Une fois que tout fonctionne correctement dans les clusters
rm -rf app/Filament/Resources/DriverResource
rm -rf app/Filament/Resources/KitchenResource
rm -rf app/Filament/Resources/PermissionAuditLogResource
rm -rf app/Filament/Resources/PermissionDelegationResource
rm -rf app/Filament/Resources/PermissionRequestResource
rm -rf app/Filament/Resources/PermissionTemplateResource
rm -rf app/Filament/Resources/PermissionWildcardResource
rm -rf app/Filament/Resources/ShopResource
rm -rf app/Filament/Resources/SupervisorResource
rm -rf app/Filament/Resources/UserResource

# Mettre à jour AdminPanelProvider pour supprimer la ligne de découverte des anciennes ressources
```

## 🔍 Clusters Créés

### Admin Panel

#### 1. BusinessCluster
- **Icône**: `heroicon-o-building-storefront`
- **Label**: "Business Management"
- **Groupe**: "Business"
- **Sort**: 1
- **Sub-navigation**: Start

#### 2. PermissionsCluster
- **Icône**: `heroicon-o-shield-check`
- **Label**: "Permissions"
- **Groupe**: "Permissions & Security"
- **Sort**: 10
- **Sub-navigation**: Top (tabs)
- **Badge**: Nombre de demandes en attente
- **Badge Color**: Dynamique (danger > 10, warning > 5, success)

#### 3. AccessControlCluster
- **Icône**: `heroicon-o-users`
- **Label**: "Access Control"
- **Groupe**: "Administration"
- **Sort**: 5
- **Badge**: Nombre total d'utilisateurs

## 🎯 Avantages de cette Architecture

1. **Isolation par Panel**: Chaque type d'utilisateur a son propre espace
2. **Organisation par Clusters**: Ressources groupées par domaine métier
3. **Navigation Intuitive**: Sous-navigation dans chaque cluster
4. **URLs Propres**: `/admin/business/shops`, `/shop/orders`, etc.
5. **Maintainability**: Code modulaire et facile à maintenir
6. **Scalabilité**: Ajout facile de nouvelles ressources/panels

## ⚠️ Points d'Attention

1. Toujours mettre à jour les namespaces après avoir déplacé des fichiers
2. Définir la propriété `$cluster` dans chaque ressource
3. Vérifier que les routes et URLs fonctionnent après migration
4. Tester les permissions et policies après la migration
5. Mettre à jour les tests si nécessaire

## 📚 Documentation Filament

- [Clusters](https://filamentphp.com/docs/4.x/panels/navigation/clusters)
- [Resources](https://filamentphp.com/docs/4.x/panels/resources)
- [Multiple Panels](https://filamentphp.com/docs/4.x/panels/configuration)

## 🚀 Commandes Utiles

```bash
# Vérifier les routes Filament
php artisan route:list --name=filament

# Clear cache
php artisan optimize:clear

# Lancer les tests
php artisan test

# Formater le code
vendor/bin/pint
```
