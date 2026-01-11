# Architecture Multi-Panels - NoFlaye MVP

## 🎯 Vue d'Ensemble

Ce projet utilise une architecture multi-panels avec Filament v4, permettant une séparation claire des responsabilités et des interfaces utilisateur selon le type d'utilisateur.

## 📊 Panels et Utilisateurs Cibles

| Panel | URL | Utilisateurs | Couleur | Fonctionnalités Principales |
|-------|-----|--------------|---------|------------------------------|
| **Admin** | `/admin` | Super Admin, Administrateurs | 🔴 Rouge | Gestion complète du système, permissions, utilisateurs, entités |
| **Shop** | `/shop` | Gérants de boutiques | 🔵 Bleu | Gestion des commandes, inventaire, produits |
| **Kitchen** | `/kitchen` | Chefs, Personnel cuisine | 🟠 Orange | Gestion de production, recettes, préparations |
| **Delivery** | `/delivery` | Livreurs, Superviseurs livraison | 🟢 Vert | Gestion des tournées, tracking, commandes |
| **Supplier** | `/supplier` | Fournisseurs | 🟣 Violet | Gestion des approvisionnements, commandes |

## 🏗️ Structure Détaillée par Panel

### 📱 Admin Panel

Le panel principal pour l'administration complète du système.

#### Clusters:

##### 🏢 Business Cluster
**Groupe**: Business Management
**Objectif**: Gestion de toutes les entités métier

**Ressources**:
- **Shops**: Gestion des boutiques
  - CRUD boutiques
  - Managers associés
  - Kitchens liées
  - Drivers assignés

- **Kitchens**: Gestion des cuisines
  - CRUD cuisines
  - Personnel
  - Shop parent

- **Suppliers**: Gestion des fournisseurs
  - CRUD fournisseurs
  - Produits fournis
  - Historique commandes

- **Drivers**: Gestion des livreurs
  - CRUD livreurs
  - Shop assigné
  - Historique livraisons

- **Supervisors**: Gestion des superviseurs
  - CRUD supervisors
  - Zones supervisées
  - Performance

##### 🔐 Permissions Cluster
**Groupe**: Permissions & Security
**Objectif**: Gestion complète du système de permissions

**Ressources**:
- **Templates**: Modèles de permissions réutilisables
- **Wildcards**: Permissions avec patterns génériques
- **Delegations**: Délégations de permissions
- **Requests**: Demandes de permissions
- **AuditLogs**: Historique et audit des permissions

**Pages Custom**:
- `MyPermissions`: Mes permissions actuelles
- `MyDelegations`: Mes délégations actives
- `AnalyticsDashboard`: Analytics et statistiques

**Widgets**:
- `PermissionStatsWidget`: Statistiques globales
- `MostUsedPermissionsWidget`: Permissions les plus utilisées
- `PermissionGrowthChart`: Évolution dans le temps
- `TemplateAdoptionWidget`: Adoption des templates

##### 👥 Access Control Cluster
**Groupe**: Administration
**Objectif**: Gestion des utilisateurs et groupes

**Ressources**:
- **Users**: Gestion des utilisateurs
  - CRUD utilisateurs
  - Permissions assignées
  - Templates appliqués
  - Groupes d'appartenance
  - Relation Managers:
    - Permissions
    - Templates
    - Delegations
    - Shops
    - Kitchens
    - Drivers
    - Supervisors
    - Suppliers

- **UserGroups**: Gestion des groupes
  - CRUD groupes
  - Hiérarchie
  - Permissions de groupe

---

### 🏪 Shop Panel

Panel pour les gérants de boutiques.

#### Clusters:

##### 🛒 Orders Cluster
**Groupe**: Orders Management
**Objectif**: Gestion des commandes

**Ressources** (à créer):
- Orders: Commandes clients
- OrderItems: Détails des commandes
- Invoices: Factures

##### 📦 Inventory Cluster
**Groupe**: Inventory
**Objectif**: Gestion de l'inventaire

**Ressources** (à créer):
- Products: Produits
- Categories: Catégories
- Stock: Gestion du stock

##### 🔐 Permissions Cluster
**Groupe**: Permissions & Security
**Objectif**: Gestion des permissions (vue simplifiée)

**Ressources**: Copie du cluster Permissions (Admin) avec restrictions

##### 👤 MyAccount Cluster
**Groupe**: My Account
**Objectif**: Gestion du compte personnel

**Pages** (à créer):
- MyProfile: Mon profil

---

### 👨‍🍳 Kitchen Panel

Panel pour le personnel de cuisine.

#### Clusters:

##### 🍳 Production Cluster
**Groupe**: Production
**Objectif**: Gestion de la production

**Ressources** (à créer):
- Recipes: Recettes
- Preparations: Préparations en cours
- Ingredients: Ingrédients

##### 🔐 Permissions Cluster
**Groupe**: Permissions & Security

##### 👤 MyAccount Cluster
**Groupe**: My Account

---

### 🚚 Delivery Panel

Panel pour les livreurs et superviseurs.

#### Clusters:

##### 🗺️ Routes Cluster
**Groupe**: Routes & Orders
**Objectif**: Gestion des tournées

**Ressources** (à créer):
- DeliveryRoutes: Tournées
- DeliveryOrders: Commandes à livrer
- Tracking: Suivi en temps réel

##### 🔐 Permissions Cluster
**Groupe**: Permissions & Security

##### 👤 MyAccount Cluster
**Groupe**: My Account

---

### 📦 Supplier Panel

Panel pour les fournisseurs.

#### Clusters:

##### 📋 Supply Cluster
**Groupe**: Supply Management
**Objectif**: Gestion de l'approvisionnement

**Ressources** (à créer):
- Supplies: Approvisionnements
- PurchaseOrders: Bons de commande
- Deliveries: Livraisons fournisseur

##### 🔐 Permissions Cluster
**Groupe**: Permissions & Security

##### 👤 MyAccount Cluster
**Groupe**: My Account

---

## 🎨 Navigation & UX

### Breadcrumbs
Chaque cluster génère des breadcrumbs automatiques :
```
Admin > Business > Shops > Create Shop
Admin > Permissions > Templates > Edit Template
Shop > Orders > Order #1234
```

### URLs
```
/admin/business/shops
/admin/business/shops/create
/admin/business/shops/1/edit
/admin/permissions/templates
/admin/permissions/requests
/admin/access-control/users

/shop/orders
/shop/inventory/products

/kitchen/production/recipes

/delivery/routes

/supplier/supply/purchase-orders
```

### Sub-Navigation
- **Business Cluster**: Position Start (sidebar gauche)
- **Permissions Cluster**: Position Top (tabs)
- **Access Control Cluster**: Position Start

### Badges
- **Permissions Cluster**: Affiche le nombre de demandes en attente
  - 🔴 Danger si > 10
  - 🟡 Warning si > 5
  - 🟢 Success sinon

- **Access Control Cluster**: Affiche le nombre total d'utilisateurs

## 🔐 Système de Permissions

### Partage des Permissions
Le système de permissions est **partagé entre tous les panels** mais avec des **vues et restrictions différentes** :

- **Admin Panel**: Accès complet au système de permissions
- **Autres Panels**: Accès restreint selon les besoins métier

### Hiérarchie
```
Templates → Permissions → Users
    ↓
Wildcards → Permissions génériques
    ↓
Delegations → Délégations temporaires
    ↓
Requests → Demandes d'accès
    ↓
AuditLogs → Traçabilité
```

## 📈 Évolutivité

### Ajouter un nouveau Panel
1. Créer le dossier `app/Filament/NewPanel/`
2. Créer `NewPanelProvider.php`
3. Enregistrer dans `bootstrap/providers.php`
4. Créer les clusters nécessaires
5. Définir les ressources

### Ajouter un nouveau Cluster
1. Créer le dossier du cluster
2. Créer la classe `ClusterName.php`
3. Ajouter `discoverClusters()` dans le PanelProvider
4. Créer les ressources dans le cluster

### Ajouter une nouvelle Ressource
```bash
php artisan make:filament-resource ResourceName \
  --panel=panelName \
  --cluster=ClusterName/ClusterClass \
  --generate
```

## 🛠️ Maintenance

### Conventions de Nommage
- **Panels**: PascalCase (Shop, Kitchen, Delivery)
- **Clusters**: PascalCase + Cluster suffix (BusinessCluster, PermissionsCluster)
- **Resources**: PascalCase + Resource suffix (ShopResource, UserResource)
- **Namespaces**: Suivre la structure des dossiers

### Organisation des Fichiers
```
Panel/
├── PanelProvider.php
├── Clusters/
│   └── ClusterName/
│       ├── ClusterNameCluster.php
│       ├── Resources/
│       │   └── ResourceName/
│       │       ├── ResourceNameResource.php
│       │       ├── Pages/
│       │       ├── Schemas/
│       │       └── Tables/
│       ├── Pages/
│       └── Widgets/
├── Pages/
└── Widgets/
```

## 🎯 Best Practices

1. **Isolation**: Chaque panel doit être autonome
2. **Réutilisation**: Utiliser `BaseResource.php` pour les comportements communs
3. **Sécurité**: Toujours vérifier les permissions par panel
4. **Performance**: Utiliser le lazy loading pour les widgets
5. **Tests**: Tester chaque panel séparément
6. **Documentation**: Documenter les spécificités de chaque panel

## 📚 Ressources

- [Filament v4 Documentation](https://filamentphp.com/docs/4.x)
- [Clusters Documentation](https://filamentphp.com/docs/4.x/panels/navigation/clusters)
- [Multi-Tenancy](https://filamentphp.com/docs/4.x/panels/tenancy)
- [Authorization](https://filamentphp.com/docs/4.x/panels/resources#authorization)
