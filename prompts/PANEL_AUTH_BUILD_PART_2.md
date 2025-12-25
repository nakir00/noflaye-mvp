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

### 3. Étendre RolePermissionSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // EXISTANTS (garder super_admin, admin, shop_manager, etc...)

        // Kitchen Manager
        $kitchenManager = Role::where('slug', 'kitchen_manager')->first();
        if ($kitchenManager) {
            $this->assignPermissions($kitchenManager, [
                'kitchens.read',
                'kitchens.update',
                'users.read',
                'users.create',
                'users.update',
                'templates.read',
                'templates.assign',
            ]);
        }

        // Kitchen Staff
        $kitchenStaff = Role::where('slug', 'kitchen_staff')->first();
        if ($kitchenStaff) {
            $this->assignPermissions($kitchenStaff, [
                'kitchens.read',
            ]);
        }

        // Driver Manager
        $driverManager = Role::where('slug', 'driver_manager')->first();
        if ($driverManager) {
            $this->assignPermissions($driverManager, [
                'drivers.read',
                'drivers.create',
                'drivers.update',
                'drivers.delete',
            ]);
        }

        // Driver
        $driver = Role::where('slug', 'driver')->first();
        if ($driver) {
            $this->assignPermissions($driver, [
                'drivers.read',
            ]);
        }

        // Supervisor Manager
        $supervisorManager = Role::where('slug', 'supervisor_manager')->first();
        if ($supervisorManager) {
            $this->assignPermissions($supervisorManager, [
                'supervisors.*',
                'shops.read',
                'kitchens.read',
                'drivers.read',
                'users.read',
                'users.create',
                'users.update',
                'templates.read',
                'templates.create',
                'templates.assign',
            ]);
        }

        $this->command->info('✅ Role permissions assigned successfully');
    }

    private function assignPermissions(Role $role, array $permissionSlugs): void
    {
        $permissions = [];

        foreach ($permissionSlugs as $slug) {
            if (str_ends_with($slug, '.*')) {
                // Wildcard: assign all permissions in group
                $groupSlug = str_replace('.*', '', $slug);
                $groupPerms = Permission::whereHas('permissionGroup', function ($q) use ($groupSlug) {
                    $q->where('slug', $groupSlug);
                })->pluck('id')->toArray();
                
                $permissions = array_merge($permissions, $groupPerms);
            } else {
                $perm = Permission::where('slug', $slug)->first();
                if ($perm) {
                    $permissions[] = $perm->id;
                }
            }
        }

        $role->permissions()->syncWithoutDetaching(array_unique($permissions));
    }
}
```

### 4. DefaultPermissionTemplateSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\DefaultPermissionTemplate;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Supervisor;
use Illuminate\Database\Seeder;

class DefaultPermissionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Template Global - New User Default
        $globalTemplate = DefaultPermissionTemplate::create([
            'name' => 'New User Default',
            'description' => 'Default permissions for new users',
            'scope_type' => null,
            'scope_id' => null,
            'is_default' => true,
        ]);

        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $globalTemplate->roles()->attach($customerRole->id);
        }

        // Template Shop Manager
        $shopManagerTemplate = DefaultPermissionTemplate::create([
            'name' => 'Shop Manager Template',
            'description' => 'Default permissions for shop managers',
            'scope_type' => 'shop',
            'scope_id' => null,
            'is_default' => true,
        ]);

        $shopManagerRole = Role::where('slug', 'shop_manager')->first();
        if ($shopManagerRole) {
            $shopManagerTemplate->roles()->attach($shopManagerRole->id);
        }

        // Template Kitchen Manager
        $kitchenManagerTemplate = DefaultPermissionTemplate::create([
            'name' => 'Kitchen Manager Template',
            'description' => 'Default permissions for kitchen managers',
            'scope_type' => 'kitchen',
            'scope_id' => null,
            'is_default' => true,
        ]);

        $kitchenManagerRole = Role::where('slug', 'kitchen_manager')->first();
        if ($kitchenManagerRole) {
            $kitchenManagerTemplate->roles()->attach($kitchenManagerRole->id);
        }

        // Template Supervisor Manager
        $supervisorTemplate = DefaultPermissionTemplate::create([
            'name' => 'Supervisor Manager Template',
            'description' => 'Default permissions for supervisors',
            'scope_type' => 'supervisor',
            'scope_id' => null,
            'is_default' => true,
        ]);

        $supervisorRole = Role::where('slug', 'supervisor_manager')->first();
        if ($supervisorRole) {
            $supervisorTemplate->roles()->attach($supervisorRole->id);
        }

        // Add permissions to templates
        $this->attachPermissionsToTemplate($shopManagerTemplate, [
            'shops.read',
            'shops.update',
            'users.read',
            'users.create',
        ]);

        $this->attachPermissionsToTemplate($kitchenManagerTemplate, [
            'kitchens.read',
            'kitchens.update',
            'users.read',
        ]);

        $this->command->info('✅ Permission templates created successfully');
    }

    private function attachPermissionsToTemplate($template, array $slugs): void
    {
        $permissionIds = Permission::whereIn('slug', $slugs)->pluck('id')->toArray();
        $template->permissions()->attach($permissionIds);
    }
}
```

### 5. PanelConfigurationSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\PanelConfiguration;
use Illuminate\Database\Seeder;

class PanelConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            [
                'panel_id' => 'admin',
                'can_manage_users' => true,
                'can_manage_roles' => true,
                'can_manage_permissions' => true,
                'can_invite_users' => true,
                'can_assign_managers' => true,
                'can_create_templates' => true,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'shop',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => true,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'kitchen',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => true,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'driver',
                'can_manage_users' => false,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => false,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => false,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'supplier',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => true,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'supervisor',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => true,
                'can_invite_users' => true,
                'can_assign_managers' => true,
                'can_create_templates' => true,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
        ];

        foreach ($configurations as $config) {
            PanelConfiguration::updateOrCreate(
                ['panel_id' => $config['panel_id']],
                $config
            );
        }

        $this->command->info('✅ Panel configurations created successfully');
    }
}
```

### 6. MultiPanelUserSeeder (Utilisateur Test)

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Driver;
use App\Models\Supervisor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MultiPanelUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating multi-panel test user...');

        // Créer utilisateur principal
        $user = User::create([
            'name' => 'Moussa Multi Manager',
            'email' => 'moussa@noflaye.sn',
            'password' => Hash::make('password'),
            'primary_role_id' => Role::where('slug', 'shop_manager')->first()->id,
        ]);

        // Créer 2 shops
        $shop1 = Shop::create([
            'name' => 'Boutique Dakar Centre',
            'slug' => 'dakar-centre',
            'phone' => '+221 77 123 45 67',
            'email' => 'dakar@noflaye.sn',
            'address' => 'Avenue Georges Pompidou, Dakar',
            'is_active' => true,
        ]);

        $shop2 = Shop::create([
            'name' => 'Boutique Plateau',
            'slug' => 'plateau',
            'phone' => '+221 77 987 65 43',
            'email' => 'plateau@noflaye.sn',
            'address' => 'Place de l\'Indépendance, Dakar',
            'is_active' => true,
        ]);

        // Créer 2 kitchens
        $kitchen1 = Kitchen::create([
            'name' => 'Cuisine Centrale Dakar',
            'slug' => 'cuisine-centrale-dakar',
            'phone' => '+221 77 555 11 22',
            'email' => 'kitchen-central@noflaye.sn',
            'address' => 'Zone Industrielle, Dakar',
            'capacity' => 50,
            'is_active' => true,
        ]);

        $kitchen2 = Kitchen::create([
            'name' => 'Cuisine Express Almadies',
            'slug' => 'cuisine-express-almadies',
            'phone' => '+221 77 555 33 44',
            'email' => 'kitchen-express@noflaye.sn',
            'address' => 'Les Almadies, Dakar',
            'capacity' => 30,
            'is_active' => true,
        ]);

        // Créer 1 driver
        $driver = Driver::create([
            'name' => 'Driver Rapide Dakar',
            'slug' => 'driver-rapide-dakar',
            'phone' => '+221 77 999 88 77',
            'email' => 'driver@noflaye.sn',
            'vehicle_type' => 'Moto',
            'vehicle_number' => 'DK-1234-AB',
            'license_number' => 'LIC-2024-001',
            'is_active' => true,
            'is_available' => true,
        ]);

        // Créer 1 supervisor
        $supervisor = Supervisor::create([
            'name' => 'Supervision Régionale Dakar',
            'slug' => 'supervision-dakar',
            'phone' => '+221 77 666 55 44',
            'email' => 'supervisor@noflaye.sn',
            'address' => 'Siège Social, Dakar',
            'is_active' => true,
        ]);

        // Attacher user aux entités
        $user->shops()->attach([$shop1->id, $shop2->id]);
        $user->kitchens()->attach([$kitchen1->id, $kitchen2->id]);
        $user->drivers()->attach($driver->id);
        $user->supervisors()->attach($supervisor->id);

        // Attacher rôles avec scopes
        $shopManagerRole = Role::where('slug', 'shop_manager')->first();
        $kitchenManagerRole = Role::where('slug', 'kitchen_manager')->first();
        $driverManagerRole = Role::where('slug', 'driver_manager')->first();
        $supervisorManagerRole = Role::where('slug', 'supervisor_manager')->first();

        $user->roles()->attach($shopManagerRole->id, [
            'scope_type' => 'shop',
            'scope_id' => $shop1->id,
            'valid_from' => now(),
            'granted_by' => 1,
            'reason' => 'Multi-panel test user setup',
        ]);

        $user->roles()->attach($kitchenManagerRole->id, [
            'scope_type' => 'kitchen',
            'scope_id' => $kitchen1->id,
            'valid_from' => now(),
            'granted_by' => 1,
            'reason' => 'Multi-panel test user setup',
        ]);

        $user->roles()->attach($driverManagerRole->id, [
            'scope_type' => 'driver',
            'scope_id' => $driver->id,
            'valid_from' => now(),
            'granted_by' => 1,
            'reason' => 'Multi-panel test user setup',
        ]);

        $user->roles()->attach($supervisorManagerRole->id, [
            'scope_type' => 'supervisor',
            'scope_id' => $supervisor->id,
            'valid_from' => now(),
            'granted_by' => 1,
            'reason' => 'Multi-panel test user setup',
        ]);

        // Créer liens cross-entités
        $shop1->kitchens()->attach([$kitchen1->id, $kitchen2->id]);
        $shop1->drivers()->attach($driver->id);
        $shop2->kitchens()->attach($kitchen1->id);
        $shop2->drivers()->attach($driver->id);
        
        $kitchen1->drivers()->attach($driver->id);
        $kitchen2->drivers()->attach($driver->id);
        
        $supervisor->shops()->attach([$shop1->id, $shop2->id]);
        $supervisor->kitchens()->attach([$kitchen1->id, $kitchen2->id]);
        $supervisor->drivers()->attach($driver->id);

        $this->command->info('');
        $this->command->info('✅ Multi-panel user created successfully!');
        $this->command->info('');
        $this->command->info('📧 Email: moussa@noflaye.sn');
        $this->command->info('🔑 Password: password');
        $this->command->info('');
        $this->command->info('📊 Access to:');
        $this->command->info('   - 2 Shops (Dakar Centre, Plateau)');
        $this->command->info('   - 2 Kitchens (Centrale, Express)');
        $this->command->info('   - 1 Driver (Rapide)');
        $this->command->info('   - 1 Supervisor (Dakar)');
        $this->command->info('');
    }
}
```

### 7. Mettre à Jour DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Ordre d'exécution important
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            DefaultPermissionTemplateSeeder::class,
            PanelConfigurationSeeder::class,
            MultiPanelUserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('');
    }
}
```

---

## 🎨 UI COMPONENTS

### PanelSwitcher Component (React/TypeScript)

**Fichier** : `resources/js/Components/PanelSwitcher.tsx`

```tsx
import { usePage } from '@inertiajs/react';
import { Fragment } from 'react';
import { Menu, Transition } from '@headlessui/react';
import {
    ChevronDownIcon,
    ShieldCheckIcon,
    BuildingStorefrontIcon,
    FireIcon,
    TruckIcon,
    CubeIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';

interface Entity {
    id: number;
    name: string;
    url: string;
    linked_shops?: string[];
    linked_kitchens?: string[];
    linked_drivers?: string[];
}

interface Panel {
    id: string;
    name: string;
    url: string;
    icon: string;
    color: string;
    entities: Entity[];
}

const iconMap: Record<string, any> = {
    'heroicon-o-shield-check': ShieldCheckIcon,
    'heroicon-o-building-storefront': BuildingStorefrontIcon,
    'heroicon-o-fire': FireIcon,
    'heroicon-o-truck': TruckIcon,
    'heroicon-o-cube': CubeIcon,
    'heroicon-o-eye': EyeIcon,
};

const colorMap: Record<string, string> = {
    danger: 'text-red-600',
    primary: 'text-blue-600',
    warning: 'text-orange-600',
    success: 'text-green-600',
    info: 'text-cyan-600',
    purple: 'text-purple-600',
};

export default function PanelSwitcher() {
    const { accessible_panels } = usePage().props as { accessible_panels: Panel[] };

    if (!accessible_panels || accessible_panels.length === 0) {
        return null;
    }

    return (
        <Menu as="div" className="relative inline-block text-left">
            <Menu.Button className="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Switch Panel
                <ChevronDownIcon className="-mr-1 h-5 w-5 text-gray-400" />
            </Menu.Button>

            <Transition
                as={Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
            >
                <Menu.Items className="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 max-h-96 overflow-y-auto focus:outline-none">
                    <div className="py-1">
                        {accessible_panels.map((panel) => {
                            const Icon = iconMap[panel.icon] || ShieldCheckIcon;
                            const colorClass = colorMap[panel.color] || 'text-gray-600';

                            return (
                                <div key={panel.id} className="border-b border-gray-100 last:border-b-0">
                                    <Menu.Item>
                                        {({ active }) => (
                                            <a
                                                href={panel.url}
                                                className={`${
                                                    active ? 'bg-gray-100' : ''
                                                } flex items-center px-4 py-3 text-sm font-medium`}
                                            >
                                                <Icon className={`mr-3 h-5 w-5 ${colorClass}`} />
                                                <span className="text-gray-900">{panel.name}</span>
                                            </a>
                                        )}
                                    </Menu.Item>

                                    {panel.entities && panel.entities.length > 0 && (
                                        <div className="pl-8 pb-2 space-y-1 bg-gray-50">
                                            {panel.entities.map((entity) => (
                                                <Menu.Item key={entity.id}>
                                                    {({ active }) => (
                                                        <div>
                                                            <a
                                                                href={entity.url}
                                                                className={`${
                                                                    active ? 'bg-gray-200' : 'bg-gray-50'
                                                                } block px-4 py-2 text-xs rounded hover:bg-gray-200 transition-colors`}
                                                            >
                                                                <span className="font-medium text-gray-900">
                                                                    {entity.name}
                                                                </span>

                                                                {/* Linked entities */}
                                                                {(entity.linked_shops?.length || 
                                                                  entity.linked_kitchens?.length || 
                                                                  entity.linked_drivers?.length) && (
                                                                    <div className="mt-1 text-xs text-gray-500 space-y-0.5">
                                                                        {entity.linked_shops && entity.linked_shops.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🏪</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_shops.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                        {entity.linked_kitchens && entity.linked_kitchens.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🔥</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_kitchens.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                        {entity.linked_drivers && entity.linked_drivers.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🚚</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_drivers.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </a>
                                                        </div>
                                                    )}
                                                </Menu.Item>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </Menu.Items>
            </Transition>
        </Menu>
    );
}
```

### Intégration dans HandleInertiaRequests

**Fichier** : `app/Http/Middleware/HandleInertiaRequests.php`

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            // Ajouter accessible_panels
            'accessible_panels' => $request->user()?->getAccessiblePanels() ?? [],
        ]);
    }
}
```

### MyPermissions Page (Tous les panels)

**Fichier** : `app/Filament/Pages/MyPermissions.php`

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MyPermissions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static string $view = 'filament.pages.my-permissions';

    protected static ?string $navigationGroup = 'My Account';

    protected static ?string $title = 'My Permissions';

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'user' => $user,
            'roles' => $user->roles()->with('permissions')->get(),
            'directPermissions' => $user->permissions,
            'inheritedPermissions' => $user->roles->flatMap->permissions->unique('id'),
            'groups' => $user->userGroups()->with('permissions')->get(),
        ];
    }
}
```

**Vue Blade** : `resources/views/filament/pages/my-permissions.blade.php`

```blade
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- User Info --}}
        <x-filament::section>
            <x-slot name="heading">
                User Information
            </x-slot>
            <x-slot name="description">
                Your account details and primary role
            </x-slot>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Name</span>
                    <p class="text-base">{{ $this->user->name }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Email</span>
                    <p class="text-base">{{ $this->user->email }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Primary Role</span>
                    <x-filament::badge color="{{ $this->user->primaryRole->color ?? 'gray' }}">
                        {{ $this->user->primaryRole->name }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        {{-- Roles --}}
        <x-filament::section>
            <x-slot name="heading">
                My Roles
            </x-slot>
            <x-slot name="description">
                Roles assigned to you with their associated scopes
            </x-slot>

            <div class="space-y-3">
                @forelse ($this->roles as $role)
                    <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <x-filament::badge color="{{ $role->color ?? 'gray' }}" class="mb-2">
                                {{ $role->name }}
                            </x-filament::badge>
                            @if ($role->pivot->scope_type)
                                <p class="text-sm text-gray-600">
                                    Scope: {{ ucfirst($role->pivot->scope_type) }}
                                    @if ($role->pivot->scope_id)
                                        (ID: {{ $role->pivot->scope_id }})
                                    @endif
                                </p>
                            @endif
                            @if ($role->pivot->valid_until)
                                <p class="text-xs text-gray-500 mt-1">
                                    Valid until: {{ $role->pivot->valid_until->format('Y-m-d H:i') }}
                                </p>
                            @endif
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $role->permissions->count() }} permissions
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No additional roles assigned</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Direct Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                Direct Permissions
            </x-slot>
            <x-slot name="description">
                Permissions assigned to you directly (not through roles)
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->directPermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="success" size="xs">
                            ✓
                        </x-filament::badge>
                        {{ $permission->name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No direct permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Inherited Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                Permissions via Roles
            </x-slot>
            <x-slot name="description">
                All permissions you have through your assigned roles
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->inheritedPermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="info" size="xs">
                            🔹
                        </x-filament::badge>
                        {{ $permission->name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No inherited permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- User Groups --}}
        @if ($this->groups->count() > 0)
            <x-filament::section>
                <x-slot name="heading">
                    My Groups
                </x-slot>
                <x-slot name="description">
                    Groups you belong to
                </x-slot>

                <div class="space-y-2">
                    @foreach ($this->groups as $group)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span class="font-medium">{{ $group->name }}</span>
                            <span class="text-xs text-gray-500">
                                {{ $group->permissions->count() }} permissions
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
```

---

## 🧪 TESTS PEST

### tests/Feature/MultiPanelUserTest.php

```php
<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Driver;
use App\Models\Supervisor;
use App\Models\Role;

test('multi-panel user can access all their panels', function () {
    $user = User::factory()->create();

    $shop = Shop::factory()->create();
    $kitchen = Kitchen::factory()->create();
    $driver = Driver::factory()->create();
    $supervisor = Supervisor::factory()->create();

    $user->shops()->attach($shop->id);
    $user->kitchens()->attach($kitchen->id);
    $user->drivers()->attach($driver->id);
    $user->supervisors()->attach($supervisor->id);

    $panels = $user->getAccessiblePanels();

    expect($panels)->toHaveCount(4);
    expect($panels[0]['id'])->toBe('shop');
    expect($panels[0]['entities'])->toHaveCount(1);
    expect($panels[1]['id'])->toBe('kitchen');
});

test('user sees managed entities in each panel', function () {
    $user = User::factory()->create();

    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $user->shops()->attach([$shop1->id, $shop2->id]);

    $managedShops = $user->getManagedShops();

    expect($managedShops)->toHaveCount(2);
    expect($user->managesShop($shop1->id))->toBeTrue();
    expect($user->managesShop($shop2->id))->toBeTrue();
});

test('template applies roles and permissions to user', function () {
    $user = User::factory()->create();
    $template = DefaultPermissionTemplate::factory()->create([
        'scope_type' => 'shop',
        'scope_id' => 1,
    ]);

    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    $template->roles()->attach($role->id);
    $template->permissions()->attach($permission->id);

    $template->applyToUser($user);

    expect($user->fresh()->roles)->toHaveCount(1);
    expect($user->fresh()->permissions)->toHaveCount(1);
});

test('cross-entity links work correctly', function () {
    $shop = Shop::factory()->create();
    $kitchen = Kitchen::factory()->create();
    $driver = Driver::factory()->create();

    $shop->kitchens()->attach($kitchen->id);
    $shop->drivers()->attach($driver->id);
    $kitchen->drivers()->attach($driver->id);

    expect($shop->kitchens)->toHaveCount(1);
    expect($shop->drivers)->toHaveCount(1);
    expect($kitchen->shops)->toHaveCount(1);
    expect($kitchen->drivers)->toHaveCount(1);
    expect($driver->shops)->toHaveCount(1);
    expect($driver->kitchens)->toHaveCount(1);
});

test('admin can see all entities', function () {
    $admin = User::factory()->create();
    $adminRole = Role::where('slug', 'super_admin')->first();
    $admin->update(['primary_role_id' => $adminRole->id]);

    Shop::factory()->count(3)->create();
    Kitchen::factory()->count(2)->create();

    expect($admin->getManagedShops())->toHaveCount(3);
    expect($admin->getManagedKitchens())->toHaveCount(2);
});

test('shop manager only sees their shops', function () {
    $manager = User::factory()->create();
    $shopManagerRole = Role::where('slug', 'shop_manager')->first();
    $manager->update(['primary_role_id' => $shopManagerRole->id]);

    $myShop = Shop::factory()->create();
    $otherShop = Shop::factory()->create();

    $manager->shops()->attach($myShop->id);

    expect($manager->getManagedShops())->toHaveCount(1);
    expect($manager->managesShop($myShop->id))->toBeTrue();
    expect($manager->managesShop($otherShop->id))->toBeFalse();
});
```

---

## ✅ CHECKLIST FINALE D'IMPLÉMENTATION

### Phase 1: Base de Données ✓
- [x] Créer 15 migrations (supervisors, kitchens, drivers, templates, configs, 10 pivots)
- [x] Vérifier foreign keys et indexes
- [x] Exécuter `php artisan migrate`

### Phase 2: Modèles ✓
- [x] Créer 5 nouveaux modèles avec relations
- [x] Étendre User avec méthodes getManaged*()
- [x] Étendre Shop/Supplier avec relations cross
- [x] Implémenter interfaces Filament (HasName)

### Phase 3: Panel Providers ✓
- [x] KitchenPanelProvider
- [x] DriverPanelProvider
- [x] SupervisorPanelProvider
- [x] Enregistrer dans bootstrap/providers.php

### Phase 4: Seeders ✓
- [x] Étendre RoleSeeder (6 nouveaux rôles)
- [x] Étendre PermissionSeeder (40+ permissions)
- [x] RolePermissionSeeder complet
- [x] DefaultPermissionTemplateSeeder
- [x] PanelConfigurationSeeder
- [x] MultiPanelUserSeeder
- [x] Mettre à jour DatabaseSeeder

### Phase 5: UI Components ✓
- [x] PanelSwitcher.tsx
- [x] Intégrer dans HandleInertiaRequests
- [x] MyPermissions page (tous panels)
- [x] Installer @headlessui/react @heroicons/react

### Phase 6: Tests ✓
- [x] Tests multi-entity user
- [x] Tests cross-links
- [x] Tests templates
- [x] Tests panel switching
- [x] Tests permissions scoped

### Phase 7: Installation & Déploiement
- [ ] `npm install @headlessui/react @heroicons/react`
- [ ] `npm run build`
- [ ] `php artisan migrate:fresh --seed`
- [ ] Test connexion moussa@noflaye.sn / password
- [ ] Vérifier panel switcher fonctionnel
- [ ] Vérifier tous les panels accessibles

---

## 🎯 CONNEXION TEST

**Email** : `moussa@noflaye.sn`  
**Password** : `password`

**Accès automatique à** :
- ✅ Admin Panel (si admin)
- ✅ 2 Shops (Dakar Centre, Plateau)
- ✅ 2 Kitchens (Centrale Dakar, Express Almadies)
- ✅ 1 Driver (Rapide Dakar)
- ✅ 1 Supervisor (Régionale Dakar)

**Panel Switcher** affichera toutes ces entités avec leurs liens croisés !

---

## 📚 COMMANDES FINALES

```bash
# Installation JS
npm install @headlessui/react @heroicons/react

# Build assets
npm run build

# Migrations & Seed
php artisan migrate:fresh --seed

# Clear caches
php artisan optimize:clear
php artisan filament:cache-components

# Démarrer serveurs
php artisan serve
npm run dev
```

---

## 🎉 RÉSULTAT FINAL

Vous aurez :
- ✅ 6 Panels Filament multi-tenant
- ✅ Architecture RBAC complète avec scopes
- ✅ Templates de permissions
- ✅ Panel Switcher fonctionnel
- ✅ Relations many-to-many flexibles
- ✅ Utilisateur test multi-panels
- ✅ UI intuitive avec liens cross-entités
- ✅ Tests complets

**🚀 Prêt pour implémentation avec Claude Code !**
