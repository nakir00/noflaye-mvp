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
            'slug' => 'new-user-default',
            'description' => 'Default permissions for new users',
            'scope_type' => null,
            'scope_id' => null,
            'is_default' => true,
            'is_active' => true,
        ]);

        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $globalTemplate->roles()->attach($customerRole->id);
        }

        // Template Shop Manager
        $shopManagerTemplate = DefaultPermissionTemplate::create([
            'name' => 'Shop Manager Template',
            'slug' => 'shop-manager-template',
            'description' => 'Default permissions for shop managers',
            'scope_type' => 'shop',
            'scope_id' => null,
            'is_default' => true,
            'is_active' => true,
        ]);

        $shopManagerRole = Role::where('slug', 'shop_manager')->first();
        if ($shopManagerRole) {
            $shopManagerTemplate->roles()->attach($shopManagerRole->id);
        }

        // Template Kitchen Manager
        $kitchenManagerTemplate = DefaultPermissionTemplate::create([
            'name' => 'Kitchen Manager Template',
            'slug' => 'kitchen-manager-template',
            'description' => 'Default permissions for kitchen managers',
            'scope_type' => 'kitchen',
            'scope_id' => null,
            'is_default' => true,
            'is_active' => true,
        ]);

        $kitchenManagerRole = Role::where('slug', 'kitchen_manager')->first();
        if ($kitchenManagerRole) {
            $kitchenManagerTemplate->roles()->attach($kitchenManagerRole->id);
        }

        // Template Supervisor Manager
        $supervisorTemplate = DefaultPermissionTemplate::create([
            'name' => 'Supervisor Manager Template',
            'slug' => 'supervisor-manager-template',
            'description' => 'Default permissions for supervisors',
            'scope_type' => 'supervisor',
            'scope_id' => null,
            'is_default' => true,
            'is_active' => true,
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
