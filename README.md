# Noflaye MVP

Plateforme multi-tenant de gestion commerciale avec architecture multi-panels Filament.

## Stack Technique

| Technologie | Version |
|-------------|---------|
| PHP | 8.4 |
| Laravel | 12 |
| Filament | 4 |
| Inertia.js | 2 |
| React | 18 |
| Livewire | 3 |
| Tailwind CSS | 3 |

## Architecture Multi-Panels

Le projet utilise une architecture multi-panels Filament avec 6 panels distincts :

| Panel | Chemin | Couleur | Description |
|-------|--------|---------|-------------|
| **Admin** | `/admin` | Rouge | Gestion globale du système |
| **Shop** | `/shop/{id}` | Bleu | Gestion des boutiques |
| **Kitchen** | `/kitchen/{id}` | Orange | Gestion des cuisines |
| **Driver** | `/driver/{id}` | Vert | Interface chauffeurs/livreurs |
| **Supervisor** | `/supervisor/{id}` | Violet | Supervision des opérations |
| **Supplier** | `/supplier/{id}` | Teal | Gestion des fournisseurs |

### Multi-Tenancy

Chaque panel (sauf Admin) utilise la multi-tenancy Filament :
- Un utilisateur peut avoir accès à plusieurs tenants de types différents
- Navigation entre panels via le menu tenant
- Isolation des données par tenant

## Système de Permissions

### Permission Templates

Le système utilise des "templates de permissions" plutôt que des rôles traditionnels :

| Template | Description | Panels accessibles |
|----------|-------------|-------------------|
| `super-admin` | Accès total | Admin |
| `admin` | Administration | Admin |
| `shop-manager` | Gérant de boutique | Shop |
| `shop-staff` | Personnel boutique | Shop |
| `kitchen-manager` | Chef de cuisine | Kitchen |
| `kitchen-staff` | Personnel cuisine | Kitchen |
| `driver` | Chauffeur/Livreur | Driver |
| `supervisor` | Superviseur | Supervisor |
| `customer` | Client | - |

### Composants du système

```
app/
├── Enums/
│   └── Permission.php          # Enum des permissions disponibles
├── Models/
│   ├── User.php                # Utilisateur avec HasTenants
│   ├── PermissionTemplate.php  # Templates de permissions
│   ├── Permission.php          # Permissions individuelles
│   └── UserPermission.php      # Attribution des permissions
├── Services/
│   └── Permissions/
│       ├── PermissionChecker.php
│       ├── ScopeManager.php
│       └── ...
└── Policies/
    ├── ShopPolicy.php
    ├── KitchenPolicy.php
    ├── DriverPolicy.php
    ├── SupervisorPolicy.php
    └── SupplierPolicy.php
```

## Modèles Principaux

| Modèle | Description | Relations |
|--------|-------------|-----------|
| `User` | Utilisateurs | shops, kitchens, drivers, supervisors, suppliers |
| `Shop` | Boutiques | users (pivot) |
| `Kitchen` | Cuisines | users (pivot) |
| `Driver` | Chauffeurs | users (pivot) |
| `Supervisor` | Superviseurs | users (pivot) |
| `Supplier` | Fournisseurs | users (pivot) |
| `Scope` | Périmètres | shops, kitchens, etc. |

## Installation

```bash
# Cloner le projet
git clone <repo-url>
cd noflaye-mvp

# Installer les dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --seed

# Build assets
npm run build

# Lancer le serveur
php artisan serve
```

## Comptes de Test

| Rôle | Email | Mot de passe | Panels |
|------|-------|--------------|--------|
| Super Admin | `super@noflaye.com` | `password` | Admin |
| Admin | `admin@noflaye.com` | `password` | Admin |
| Shop Manager | `alice@noflaye.com` | `password` | Shop |
| Kitchen Manager | `bob@noflaye.com` | `password` | Kitchen |
| Multi-accès | `charlie@noflaye.com` | `password` | Shop, Driver |
| Multi-accès | `frank@noflaye.com` | `password` | Shop (x2), Driver |
| **Multi Shop** | `multishop@noflaye.com` | `password` | Shop (x4), Driver (x2), Supervisor |
| Driver | `grace@noflaye.com` | `password` | Driver |
| Supervisor | `eve@noflaye.com` | `password` | Supervisor |

## Navigation Multi-Panels

Les utilisateurs avec accès à plusieurs panels/tenants peuvent naviguer via le **menu tenant** :

1. Cliquer sur le nom du tenant actuel dans la sidebar
2. Le dropdown affiche tous les tenants accessibles :
   - `boutique Perfect Shoes`
   - `driver Driver Perfect Shoes`
   - `superviseur Superviseur Almamy Bijouterie`
3. Cliquer pour naviguer vers l'espace souhaité

## Structure des Fichiers

```
app/
├── Filament/
│   ├── Admin/           # Panel Admin
│   │   └── Clusters/
│   │       ├── AccessControl/
│   │       └── Business/
│   ├── Shop/            # Panel Shop
│   │   └── Clusters/
│   ├── Kitchen/         # Panel Kitchen
│   │   └── Clusters/
│   ├── Driver/          # Panel Driver
│   │   └── Clusters/
│   ├── Supervisor/      # Panel Supervisor
│   │   └── Clusters/
│   └── Supplier/        # Panel Supplier
│       └── Clusters/
├── Models/
├── Policies/
├── Providers/
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php
│   └── Filament/
│       ├── AdminPanelProvider.php
│       ├── ShopPanelProvider.php
│       ├── KitchenPanelProvider.php
│       ├── DriverPanelProvider.php
│       ├── SupervisorPanelProvider.php
│       └── SupplierPanelProvider.php
└── Services/
    ├── PanelNavigationService.php
    └── Permissions/
```

## Commandes Utiles

```bash
# Rafraîchir la base avec les seeds
php artisan migrate:fresh --seed

# Vider les caches
php artisan optimize:clear

# Formater le code
vendor/bin/pint

# Analyse statique
vendor/bin/phpstan analyse

# Tests
php artisan test
```

## Packages Principaux

- **filament/filament** - Framework admin panels
- **spatie/laravel-activitylog** - Journalisation des activités
- **spatie/laravel-data** - DTOs et transformations
- **spatie/laravel-query-builder** - API query building
- **lorisleiva/laravel-actions** - Actions réutilisables
- **inertiajs/inertia-laravel** - SPA sans API

## Développement

### Ajouter un nouveau Panel

1. Créer le PanelProvider dans `app/Providers/Filament/`
2. Enregistrer dans `bootstrap/providers.php`
3. Créer la structure dans `app/Filament/{PanelName}/`
4. Ajouter le template de permissions correspondant
5. Mettre à jour `PanelNavigationService` si nécessaire

### Ajouter une Permission

1. Ajouter le cas dans `app/Enums/Permission.php`
2. Mettre à jour les templates concernés
3. Utiliser dans les policies avec `$user->hasPermission(Permission::CASE)`

## Documentation

- [Système d'autorisation](.claude/docs/authorization-system-complete.md)
- [Implémentation des autorisations](.claude/docs/authorization-implementation.md)

## Licence

Propriétaire - Noflaye - Tous droits réservés
