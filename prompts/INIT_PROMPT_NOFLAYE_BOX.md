# Initialisation Projet Noflaye Box - Laravel

## 🎯 Contexte

Je démarre un nouveau projet Laravel pour **Noflaye Box**, une plateforme de livraison de nourriture au Sénégal. C'est une migration d'un ancien projet AdonisJS vers Laravel.

Le projet nécessite une architecture **hybride** :
- **Backend Admin** : Filament PHP (Livewire) pour les panels administratifs
- **Frontend Client** : Inertia.js + React + TypeScript pour l'application client
- **Multi-tenancy** : Système de gestion multi-boutiques
- **Multi-panels** : 5 panels Filament (Admin, Shop, Kitchen, Driver, Supplier)

---

## 📦 Stack Technique Requise

### Backend
- ✅ **Laravel 12** (déjà installé)
- ✅ **PHP 8.2** (déjà installé)
- ✅ **MySQL/MariaDB**

### Frontend Admin (Filament)
- **Filament v4** (dernier stable)
- **Livewire v3**
- **Alpine.js** (via Filament)
- **Tailwind CSS** (via Filament)

### Frontend Client (Inertia)
- **Inertia.js v2** (dernier stable - SSR ready)
- **React 18+**
- **TypeScript**
- **Tailwind CSS**
- **shadcn/ui** (composants UI React)
- **Vite** (bundler)

### Packages Additionnels
- **Laravel Sanctum** (authentification API + sessions)
- **Spatie Laravel Permission** (gestion permissions - optionnel si on fait custom)
- **Laravel Debugbar** (dev only)
- **Laravel IDE Helper** (dev only)

---

## 🎨 Configuration Frontend Détaillée

### Inertia.js + React

**Configuration TypeScript** :
```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "paths": {
      "@/*": ["./resources/js/*"]
    }
  },
  "include": ["resources/js/**/*.ts", "resources/js/**/*.tsx"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

**Package.json scripts** (inspiré de l'ancien projet AdonisJS) :
```json
{
  "scripts": {
    "dev": "vite",
    "build": "tsc && vite build",
    "preview": "vite preview",
    "type-check": "tsc --noEmit"
  },
  "dependencies": {
    "@inertiajs/react": "^2.0.0",
    "react": "^18.3.0",
    "react-dom": "^18.3.0",
    "axios": "^1.7.0"
  },
  "devDependencies": {
    "@types/react": "^18.3.0",
    "@types/react-dom": "^18.3.0",
    "@vitejs/plugin-react": "^4.3.0",
    "typescript": "^5.6.0",
    "vite": "^6.0.0",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.0",
    "postcss": "^8.4.0"
  }
}
```

**Structure des dossiers Inertia** :
```
resources/
├── js/
│   ├── app.tsx                 # Point d'entrée Inertia
│   ├── Components/             # Composants React réutilisables
│   │   ├── ui/                # shadcn/ui components
│   │   ├── Layout/            # Layouts (Header, Footer, Sidebar)
│   │   └── Shared/            # Composants partagés
│   ├── Pages/                 # Pages Inertia (routes)
│   │   ├── Auth/
│   │   │   ├── Login.tsx
│   │   │   └── Register.tsx
│   │   ├── Home.tsx
│   │   ├── Products/
│   │   ├── Cart/
│   │   └── Orders/
│   ├── Layouts/               # Layouts principaux
│   │   ├── AppLayout.tsx
│   │   └── GuestLayout.tsx
│   ├── types/                 # Types TypeScript
│   │   ├── index.d.ts
│   │   └── models.d.ts
│   └── lib/                   # Utilitaires
│       └── utils.ts
├── css/
│   └── app.css               # Styles globaux Tailwind
└── views/
    └── app.blade.php         # Template Blade pour Inertia
```

### shadcn/ui

**Installation** :
```bash
npx shadcn-ui@latest init
```

**Configuration shadcn** (composants requis initialement) :
- `button`
- `card`
- `input`
- `label`
- `dialog`
- `dropdown-menu`
- `avatar`
- `badge`
- `toast`

### Tailwind CSS

**tailwind.config.js** :
```js
/** @type {import('tailwindcss').Config} */
export default {
  darkMode: ['class'],
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.tsx',
    './resources/js/**/*.ts',
  ],
  theme: {
    extend: {
      colors: {
        border: 'hsl(var(--border))',
        input: 'hsl(var(--input))',
        ring: 'hsl(var(--ring))',
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        primary: {
          DEFAULT: 'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
        },
        // ... autres couleurs shadcn
      },
      borderRadius: {
        lg: 'var(--radius)',
        md: 'calc(var(--radius) - 2px)',
        sm: 'calc(var(--radius) - 4px)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('tailwindcss-animate'),
  ],
}
```

---

## 🔧 Configuration Filament

### Installation Multi-Panels

**5 Panels requis** :
1. **Admin Panel** (`/admin`) - Super Admin & Admin
2. **Shop Panel** (`/shop`) - Shop Managers (multi-tenant par boutique)
3. **Kitchen Panel** (`/kitchen`) - Kitchen Staff (multi-tenant par boutique)
4. **Driver Panel** (`/driver`) - Drivers
5. **Supplier Panel** (`/supplier`) - Fournisseurs d'ingrédients (multi-tenant par fournisseur)

**Commandes d'installation** :
```bash
# Installer Filament
composer require filament/filament:"^4.0"

# Créer les panels
php artisan make:filament-panel admin
php artisan make:filament-panel shop
php artisan make:filament-panel kitchen
php artisan make:filament-panel driver
php artisan make:filament-panel supplier
```

**Configuration des Panels** :

Chaque panel doit avoir :
- Path spécifique (`/admin`, `/shop`, `/kitchen`, `/driver`, `/supplier`)
- Login séparé (même authentification Laravel mais UI différente)
- Couleurs thème différentes
- Multi-tenancy activé pour Shop, Kitchen et Supplier panels
  - Shop & Kitchen : tenant = Shop model
  - Supplier : tenant = Supplier model

**PanelProvider exemple (Shop)** :
```php
use Filament\Panel;
use App\Models\Shop;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('shop')
        ->path('shop')
        ->login()
        ->tenant(Shop::class)
        ->tenantRoutePrefix('boutique')
        ->colors([
            'primary' => '#4ECDC4',
        ])
        ->discoverResources(in: app_path('Filament/Shop/Resources'), for: 'App\\Filament\\Shop\\Resources')
        ->middleware([
            'web',
            'auth',
        ]);
}
```

**PanelProvider exemple (Supplier)** :
```php
use Filament\Panel;
use App\Models\Supplier;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('supplier')
        ->path('supplier')
        ->login()
        ->tenant(Supplier::class)
        ->tenantRoutePrefix('fournisseur')
        ->colors([
            'primary' => '#9B59B6',
        ])
        ->discoverResources(in: app_path('Filament/Supplier/Resources'), for: 'App\\Filament\\Supplier\\Resources')
        ->middleware([
            'web',
            'auth',
        ]);
}
```

---

## 🗃️ Base de Données - Migrations Prioritaires

**À créer dans cet ordre** :

### Phase 1 : Authentification & Autorisations
1. `users` (modifier migration existante)
2. `roles`
3. `permissions`
4. `permission_groups`
5. `role_permissions`
6. `user_roles`
7. `user_groups`
8. `user_group_permissions`
9. `user_group_members`

### Phase 2 : Business Core
10. `shops` (boutiques)
11. `suppliers` (fournisseurs d'ingrédients)
12. `regions`
13. `categories`
14. `products`
15. `ingredients`

### Phase 3 : Métier Complet (à faire plus tard)
- Orders, Deliveries, Inventory, etc.

**Note** : Pour l'instant, on se concentre sur l'authentification et l'infrastructure. Les tables métier viendront après.

---

## 🎯 Structure Projet Attendue

```
app/
├── Filament/
│   ├── Admin/                  # Panel Admin
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   ├── Shop/                   # Panel Shop
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   ├── Kitchen/                # Panel Kitchen
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   ├── Driver/                 # Panel Driver
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   └── Supplier/               # Panel Supplier
│       ├── Resources/
│       ├── Pages/
│       └── Widgets/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   └── Api/
│   ├── Middleware/
│   │   ├── FilamentAdminAccess.php
│   │   ├── FilamentShopAccess.php
│   │   ├── FilamentKitchenAccess.php
│   │   ├── FilamentDriverAccess.php
│   │   └── FilamentSupplierAccess.php
│   └── Requests/
├── Models/
│   ├── User.php               # Implements HasTenants, FilamentUser
│   ├── Role.php
│   ├── Permission.php
│   ├── Shop.php               # Tenant model (Shop & Kitchen panels)
│   ├── Supplier.php           # Tenant model (Supplier panel)
│   └── ...
├── Policies/
│   ├── OrderPolicy.php
│   ├── ShopPolicy.php
│   └── SupplierPolicy.php
└── Providers/
    ├── Filament/
    │   ├── AdminPanelProvider.php
    │   ├── ShopPanelProvider.php
    │   ├── KitchenPanelProvider.php
    │   ├── DriverPanelProvider.php
    │   └── SupplierPanelProvider.php
    └── AppServiceProvider.php

resources/
├── js/
│   ├── app.tsx                # Inertia entry point
│   ├── Components/
│   │   └── ui/                # shadcn/ui
│   ├── Pages/
│   │   ├── Auth/
│   │   └── Home.tsx
│   ├── Layouts/
│   └── types/
├── css/
│   └── app.css
└── views/
    ├── app.blade.php          # Inertia root
    └── filament/              # Filament customizations

routes/
├── web.php                    # Routes Inertia (clients)
├── api.php                    # API routes (Sanctum)
└── auth.php                   # Routes d'authentification

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_sessions_table.php
│   ├── 2024_12_22_000001_create_roles_table.php
│   ├── 2024_12_22_000002_create_permissions_table.php
│   ├── 2024_12_22_000003_create_role_permissions_table.php
│   ├── 2024_12_22_000004_create_user_roles_table.php
│   └── 2024_12_22_000010_create_shops_table.php
├── seeders/
│   ├── RoleSeeder.php
│   ├── PermissionSeeder.php
│   └── DatabaseSeeder.php
└── factories/
    ├── UserFactory.php
    └── ShopFactory.php
```

---

## 📋 Tâches d'Installation

### Étape 1 : Packages Backend
```bash
# Filament v4
composer require filament/filament:"^4.0"

# Sanctum (si pas déjà installé)
php artisan install:api

# Dev packages
composer require --dev laravel/pint barryvdh/laravel-debugbar
composer require --dev barryvdh/laravel-ide-helper
```

### Étape 2 : Packages Frontend
```bash
# Inertia.js v2
composer require inertiajs/inertia-laravel:"^2.0"
composer require tightenco/ziggy

# NPM packages
npm install @inertiajs/react@^2.0.0 react@^18.3.0 react-dom@^18.3.0
npm install -D @types/react @types/react-dom
npm install -D @vitejs/plugin-react typescript
npm install -D tailwindcss postcss autoprefixer
npm install -D @tailwindcss/forms tailwindcss-animate

# shadcn/ui
npx shadcn@latest init
npx shadcn@latest add button card input label dialog dropdown-menu avatar badge toast
```

### Étape 3 : Configuration
```bash
# Publier configs
php artisan vendor:publish --tag=filament-config
php artisan vendor:publish --tag=filament-assets

# Créer panels Filament
php artisan make:filament-panel admin
php artisan make:filament-panel shop
php artisan make:filament-panel kitchen
php artisan make:filament-panel driver
php artisan make:filament-panel supplier

# Créer middleware d'accès
php artisan make:middleware FilamentAdminAccess
php artisan make:middleware FilamentShopAccess
php artisan make:middleware FilamentKitchenAccess
php artisan make:middleware FilamentDriverAccess
php artisan make:middleware FilamentSupplierAccess
```

### Étape 4 : Base de Données
```bash
# Créer migrations
php artisan make:migration create_roles_table
php artisan make:migration create_permissions_table
php artisan make:migration create_permission_groups_table
php artisan make:migration create_role_permissions_table
php artisan make:migration create_user_roles_table
php artisan make:migration create_user_groups_table
php artisan make:migration create_user_group_permissions_table
php artisan make:migration create_user_group_members_table
php artisan make:migration create_shops_table
php artisan make:migration create_suppliers_table

# Modifier migration users existante
# Ajouter: primary_role_id, etc.

# Créer seeders
php artisan make:seeder RoleSeeder
php artisan make:seeder PermissionSeeder
php artisan make:seeder ShopSeeder
php artisan make:seeder SupplierSeeder
```

### Étape 5 : Modèles & Relations
```bash
# Créer modèles
php artisan make:model Role
php artisan make:model Permission
php artisan make:model PermissionGroup
php artisan make:model UserGroup
php artisan make:model Shop --factory
php artisan make:model Supplier --factory

# Créer policies
php artisan make:policy ShopPolicy --model=Shop
php artisan make:policy SupplierPolicy --model=Supplier
php artisan make:policy UserPolicy --model=User
```

---

## 🔑 Modifications Spécifiques

### Modèle User

Doit implémenter :
- `Filament\Models\Contracts\FilamentUser`
- `Filament\Models\Contracts\HasTenants`

Et contenir :
```php
public function canAccessPanel(Panel $panel): bool;
public function getTenants(Panel $panel): Collection;
public function canAccessTenant(Model $tenant): bool;
```

### Routes Web (Inertia)

Séparer clairement :
- Routes Filament (gérées automatiquement)
- Routes Inertia (pour clients)

```php
// routes/web.php
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Auth routes pour Inertia (séparées de Filament)
require __DIR__.'/auth.php';
```

### Configuration Vite

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
});
```

### Middleware HandleInertiaRequests

```php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->fullName,
                    'email' => $request->user()->email,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'ziggy' => fn () => [
                ...\Ziggy\Ziggy::generate(),
            ],
        ]);
    }
}
```

---

## ✅ Checklist de Validation

Après l'installation, vérifier :

- [ ] `php artisan serve` démarre sans erreur
- [ ] `npm run dev` compile sans erreur
- [ ] Accès à `/admin` affiche login Filament
- [ ] Accès à `/shop` affiche login Filament
- [ ] Accès à `/kitchen` affiche login Filament
- [ ] Accès à `/driver` affiche login Filament
- [ ] Accès à `/supplier` affiche login Filament
- [ ] Accès à `/` affiche page Inertia React
- [ ] TypeScript compile sans erreur (`npm run type-check`)
- [ ] Tailwind CSS fonctionne (styles visibles)
- [ ] shadcn/ui composants disponibles
- [ ] Migrations s'exécutent sans erreur
- [ ] Seeders créent les données de base
- [ ] Tests passent (`php artisan test`)

---

## 🚀 Prochaines Étapes (Après Setup)

Une fois l'installation terminée, nous implémenterons :

1. **Système d'autorisation complet**
   - RBAC (rôles)
   - GBAC (groupes)
   - Permissions directes
   - Context rules

2. **Multi-tenancy Filament**
   - Tenant switcher
   - Filtrage automatique par boutique
   - Policies par panel

3. **Interface Inertia**
   - Authentification client
   - Catalogue produits
   - Panier
   - Commandes

4. **Tables métier**
   - Products, Orders, Deliveries
   - Inventory, Payments, etc.

---

## 📝 Notes Importantes

- **Filament v4** : Dernière version majeure avec améliorations performance et nouvelles features
- **Inertia v2** : Support SSR (Server-Side Rendering) natif - on peut l'activer plus tard si besoin
- **Pas de Spatie Permission** pour l'instant : On va créer notre propre système custom pour plus de flexibilité
- **Deux systèmes d'auth séparés** : Filament utilise sa propre auth, Inertia utilise l'auth Laravel standard
- **Sanctum pour API** : Prêt pour une future app mobile
- **Multi-rôles** : Un user peut avoir plusieurs rôles (Driver + Kitchen Staff par exemple)
- **Multi-panels** : 5 panels avec multi-tenancy pour Shop, Kitchen et Supplier
- **Convention de nommage** : 
  - Migrations : `YYYY_MM_DD_HHMMSS_create_table_name.php`
  - Modèles : Singular PascalCase (`User`, `Shop`, `Order`, `Supplier`)
  - Tables : Plural snake_case (`users`, `shops`, `orders`, `suppliers`)

---

## 🎯 Question

Peux-tu procéder à l'installation complète du projet selon ces spécifications ?

Commence par :
1. Installer tous les packages (Composer + NPM)
   - Filament v4
   - Inertia v2
   - React 18 + TypeScript
   - shadcn/ui
2. Créer les configurations (TypeScript, Tailwind, Vite)
3. Créer les 5 panels Filament (Admin, Shop, Kitchen, Driver, Supplier)
4. Créer les middleware d'accès pour chaque panel
5. Créer les migrations de base (users, roles, permissions, shops, suppliers)
6. Créer la structure Inertia (app.tsx, layouts, pages de base)

Je te dirai ensuite quand passer aux étapes suivantes !

Merci ! 🚀
