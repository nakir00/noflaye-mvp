<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionTemplate;
use Illuminate\Database\Seeder;

class PermissionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temporarily unregister observer to avoid wildcard issues
        PermissionTemplate::unsetEventDispatcher();

        $this->command->info('Creating Permission Templates...');

        // Create Customer Template
        $customer = PermissionTemplate::updateOrCreate(
            ['slug' => 'customer'],
            [
                'name' => 'Customer',
                'description' => 'Basic customer access - can place orders and view their account',
                'color' => '#10b981',
                'icon' => 'heroicon-o-user',
                'level' => 1,
                'sort_order' => 10,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ]
        );

        // Create Manager Template
        $manager = PermissionTemplate::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Shop and kitchen manager - can manage operations',
                'color' => '#3b82f6',
                'icon' => 'heroicon-o-briefcase',
                'level' => 5,
                'sort_order' => 20,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ]
        );

        // Create Admin Template
        $admin = PermissionTemplate::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'System administrator - full access to all features',
                'color' => '#ef4444',
                'icon' => 'heroicon-o-shield-check',
                'level' => 10,
                'sort_order' => 30,
                'is_active' => true,
                'is_system' => true,
                'auto_sync_users' => true,
            ]
        );

        $this->command->info('Assigning permissions to templates...');

        // Customer permissions - basic read access
        $customerPermissions = Permission::whereIn('slug', [
            'orders.read',
            'orders.create',
            'products.read',
        ])->pluck('id');

        if ($customerPermissions->isNotEmpty()) {
            $customer->permissions()->sync($customerPermissions->mapWithKeys(fn($id) => [
                $id => ['source' => 'direct', 'sort_order' => 1]
            ]));
        }

        // Manager permissions - operational access
        $managerPermissions = Permission::whereIn('slug', [
            'orders.read',
            'orders.create',
            'orders.update',
            'orders.cancel',
            'products.read',
            'products.create',
            'products.update',
            'inventory.read',
            'inventory.update',
            'inventory.restock',
            'kitchen.orders.read',
            'kitchen.orders.prepare',
            'deliveries.read',
            'deliveries.assign',
            'deliveries.update',
            'analytics.shop.read',
            'users.read',
            'shops.read',
            'kitchens.read',
            'drivers.read',
        ])->pluck('id');

        if ($managerPermissions->isNotEmpty()) {
            $manager->permissions()->sync($managerPermissions->mapWithKeys(fn($id) => [
                $id => ['source' => 'direct', 'sort_order' => 1]
            ]));
        }

        // Admin permissions - all permissions
        $adminPermissions = Permission::all()->pluck('id');

        if ($adminPermissions->isNotEmpty()) {
            $admin->permissions()->sync($adminPermissions->mapWithKeys(fn($id) => [
                $id => ['source' => 'direct', 'sort_order' => 1]
            ]));
        }

        $this->command->info('✅ Permission Templates created successfully');
        $this->command->info("   - Customer: {$customerPermissions->count()} permissions");
        $this->command->info("   - Manager: {$managerPermissions->count()} permissions");
        $this->command->info("   - Admin: {$adminPermissions->count()} permissions");
    }
}
