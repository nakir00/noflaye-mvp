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
