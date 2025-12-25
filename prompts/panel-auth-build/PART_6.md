# NOFLAYE BOX - IMPLÉMENTATION COMPLÈTE PART 2

> **Suite du document principal** - Panel Providers, Seeders, UI Components, Tests

---

## 🎛️ PANEL PROVIDERS (Suite et Complet)

### KitchenPanelProvider

```php
<?php

namespace App\Providers\Filament;

use App\Models\Kitchen;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class KitchenPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('kitchen')
            ->path('kitchen')
            ->login()
            ->tenant(Kitchen::class)
            ->colors([
                'primary' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Kitchen/Resources'), for: 'App\\Filament\\Kitchen\\Resources')
            ->discoverPages(in: app_path('Filament/Kitchen/Pages'), for: 'App\\Filament\\Kitchen\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Kitchen/Widgets'), for: 'App\\Filament\\Kitchen\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### DriverPanelProvider

```php
<?php

namespace App\Providers\Filament;

use App\Models\Driver;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DriverPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('driver')
            ->path('driver')
            ->login()
            ->tenant(Driver::class, ownershipRelationship: 'users')
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Driver/Resources'), for: 'App\\Filament\\Driver\\Resources')
            ->discoverPages(in: app_path('Filament/Driver/Pages'), for: 'App\\Filament\\Driver\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Driver/Widgets'), for: 'App\\Filament\\Driver\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### SupervisorPanelProvider

```php
<?php

namespace App\Providers\Filament;

use App\Models\Supervisor;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SupervisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('supervisor')
            ->path('supervisor')
            ->login()
            ->tenant(Supervisor::class)
            ->colors([
                'primary' => Color::Purple,
            ])
            ->discoverResources(in: app_path('Filament/Supervisor/Resources'), for: 'App\\Filament\\Supervisor\\Resources')
            ->discoverPages(in: app_path('Filament/Supervisor/Pages'), for: 'App\\Filament\\Supervisor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Supervisor/Widgets'), for: 'App\\Filament\\Supervisor\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### Enregistrement dans bootstrap/providers.php

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\ShopPanelProvider::class,
    App\Providers\Filament\KitchenPanelProvider::class,
    App\Providers\Filament\DriverPanelProvider::class,
    App\Providers\Filament\SupplierPanelProvider::class,
    App\Providers\Filament\SupervisorPanelProvider::class,
];
```

---

## 🌱 SEEDERS COMPLETS

### 1. Étendre RoleSeeder (Ajouter Nouveaux Rôles)

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // EXISTANTS (garder ceux déjà créés)
            // ... super_admin, admin, shop_manager, shop_staff, supplier_manager, supplier_staff, customer ...

            // NOUVEAUX RÔLES - Kitchen
            [
                'name' => 'Kitchen Manager',
                'slug' => 'kitchen_manager',
                'description' => 'Manage kitchen operations and staff',
                'level' => 75,
                'active' => true,
                'is_system' => false,
                'color' => 'orange',
            ],
            [
                'name' => 'Kitchen Staff',
                'slug' => 'kitchen_staff',
                'description' => 'Kitchen staff member',
                'level' => 60,
                'active' => true,
                'is_system' => false,
                'color' => 'orange',
            ],

            // NOUVEAUX RÔLES - Driver
            [
                'name' => 'Driver Manager',
                'slug' => 'driver_manager',
                'description' => 'Manage drivers and delivery operations',
                'level' => 70,
                'active' => true,
                'is_system' => false,
                'color' => 'green',
            ],
            [
                'name' => 'Driver',
                'slug' => 'driver',
                'description' => 'Delivery driver',
                'level' => 40,
                'active' => true,
                'is_system' => false,
                'color' => 'green',
            ],

            // NOUVEAUX RÔLES - Supervisor
            [
                'name' => 'Supervisor Manager',
                'slug' => 'supervisor_manager',
                'description' => 'Regional supervisor managing multiple entities',
                'level' => 85,
                'active' => true,
                'is_system' => false,
                'color' => 'purple',
            ],
            [
                'name' => 'Supervisor Staff',
                'slug' => 'supervisor_staff',
                'description' => 'Supervisor staff member',
                'level' => 70,
                'active' => true,
                'is_system' => false,
                'color' => 'purple',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('✅ Roles created/updated successfully');
    }
}
```

### 2. Étendre PermissionSeeder (Ajouter Permissions Kitchen/Driver/Supervisor)

```php
<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Créer groupes de permissions
        $this->createPermissionGroups();

        // Créer permissions
        $this->createPermissions();

        $this->command->info('✅ Permissions created successfully');
    }

    private function createPermissionGroups(): void
    {
        $groups = [
            // EXISTANTS
            ['name' => 'Users', 'slug' => 'users'],
            ['name' => 'Roles', 'slug' => 'roles'],
            ['name' => 'Permissions', 'slug' => 'permissions'],
            ['name' => 'Groups', 'slug' => 'groups'],
            ['name' => 'Shops', 'slug' => 'shops'],
            ['name' => 'Suppliers', 'slug' => 'suppliers'],

            // NOUVEAUX GROUPES
            ['name' => 'Kitchens', 'slug' => 'kitchens'],
            ['name' => 'Drivers', 'slug' => 'drivers'],
            ['name' => 'Supervisors', 'slug' => 'supervisors'],
            ['name' => 'Templates', 'slug' => 'templates'],
            ['name' => 'Panel Configurations', 'slug' => 'panel_configurations'],
        ];

        foreach ($groups as $group) {
            PermissionGroup::updateOrCreate(
                ['slug' => $group['slug']],
                $group
            );
        }
    }

    private function createPermissions(): void
    {
        $permissions = [
            // EXISTANTS (garder users, roles, permissions, groups, shops, suppliers...)
            
            // KITCHENS
            [
                'name' => 'View Kitchens',
                'slug' => 'kitchens.read',
                'group' => 'kitchens',
                'action_type' => 'read',
            ],
            [
                'name' => 'Create Kitchens',
                'slug' => 'kitchens.create',
                'group' => 'kitchens',
                'action_type' => 'create',
            ],
            [
                'name' => 'Update Kitchens',
                'slug' => 'kitchens.update',
                'group' => 'kitchens',
                'action_type' => 'update',
            ],
            [
                'name' => 'Delete Kitchens',
                'slug' => 'kitchens.delete',
                'group' => 'kitchens',
                'action_type' => 'delete',
            ],

            // DRIVERS
            [
                'name' => 'View Drivers',
                'slug' => 'drivers.read',
                'group' => 'drivers',
                'action_type' => 'read',
            ],
            [
                'name' => 'Create Drivers',
                'slug' => 'drivers.create',
                'group' => 'drivers',
                'action_type' => 'create',
            ],
            [
                'name' => 'Update Drivers',
                'slug' => 'drivers.update',
                'group' => 'drivers',
                'action_type' => 'update',
            ],
            [
                'name' => 'Delete Drivers',
                'slug' => 'drivers.delete',
                'group' => 'drivers',
                'action_type' => 'delete',
            ],

            // SUPERVISORS
            [
                'name' => 'View Supervisors',
                'slug' => 'supervisors.read',
                'group' => 'supervisors',
                'action_type' => 'read',
            ],
            [
                'name' => 'Create Supervisors',
                'slug' => 'supervisors.create',
                'group' => 'supervisors',
                'action_type' => 'create',
            ],
            [
                'name' => 'Update Supervisors',
                'slug' => 'supervisors.update',
                'group' => 'supervisors',
                'action_type' => 'update',
            ],
            [
                'name' => 'Delete Supervisors',
                'slug' => 'supervisors.delete',
                'group' => 'supervisors',
                'action_type' => 'delete',
            ],

            // TEMPLATES
            [
                'name' => 'View Templates',
                'slug' => 'templates.read',
                'group' => 'templates',
                'action_type' => 'read',
            ],
            [
                'name' => 'Create Templates',
                'slug' => 'templates.create',
                'group' => 'templates',
                'action_type' => 'create',
            ],
            [
                'name' => 'Assign Templates',
                'slug' => 'templates.assign',
                'group' => 'templates',
                'action_type' => 'create',
            ],
            [
                'name' => 'Set Default Template',
                'slug' => 'templates.default.set',
                'group' => 'templates',
                'action_type' => 'update',
            ],

            // PANEL CONFIGURATIONS
            [
                'name' => 'View Panel Configs',
                'slug' => 'panel_configurations.read',
                'group' => 'panel_configurations',
                'action_type' => 'read',
            ],
            [
                'name' => 'Update Panel Configs',
                'slug' => 'panel_configurations.update',
                'group' => 'panel_configurations',
                'action_type' => 'update',
            ],
        ];

        foreach ($permissions as $permData) {
            $group = PermissionGroup::where('slug', $permData['group'])->first();
            
            Permission::updateOrCreate(
                ['slug' => $permData['slug']],
                [
                    'name' => $permData['name'],
                    'permission_group_id' => $group?->id,
                    'action_type' => $permData['action_type'],
                ]
            );
        }
    }
}
```
