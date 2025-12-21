<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - Toutes les permissions
        $superAdmin = Role::where('slug', 'super_admin')->first();
        if ($superAdmin) {
            $allPermissions = Permission::all()->pluck('id');
            $superAdmin->permissions()->sync($allPermissions);
        }

        // Admin - Toutes sauf certaines permissions système critiques
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::whereNotIn('slug', [
                'settings.permissions.manage', // Seul super_admin peut gérer les permissions
            ])->pluck('id');
            $admin->permissions()->sync($adminPermissions);
        }

        // Shop Manager Senior
        $shopManagerSenior = Role::where('slug', 'shop_manager_senior')->first();
        if ($shopManagerSenior) {
            $this->assignPermissions($shopManagerSenior, [
                'orders.*',
                'products.*',
                'inventory.*',
                'deliveries.*',
                'analytics.shop.read',
                'analytics.reports.export',
                'users.read',
                'users.create',
                'users.update',
            ]);
        }

        // Shop Manager
        $shopManager = Role::where('slug', 'shop_manager')->first();
        if ($shopManager) {
            $this->assignPermissions($shopManager, [
                'orders.read',
                'orders.create',
                'orders.update',
                'orders.cancel',
                'products.read',
                'products.update',
                'products.pricing.update',
                'inventory.read',
                'inventory.update',
                'inventory.restock',
                'deliveries.read',
                'deliveries.assign',
                'analytics.shop.read',
            ]);
        }

        // Shop Manager Junior
        $shopManagerJunior = Role::where('slug', 'shop_manager_junior')->first();
        if ($shopManagerJunior) {
            $this->assignPermissions($shopManagerJunior, [
                'orders.read',
                'orders.create',
                'orders.update',
                'products.read',
                'inventory.read',
                'inventory.update',
                'deliveries.read',
            ]);
        }

        // Shop Manager Trainee
        $shopManagerTrainee = Role::where('slug', 'shop_manager_trainee')->first();
        if ($shopManagerTrainee) {
            $this->assignPermissions($shopManagerTrainee, [
                'orders.read',
                'products.read',
                'inventory.read',
            ]);
        }

        // Kitchen Manager
        $kitchenManager = Role::where('slug', 'kitchen_manager')->first();
        if ($kitchenManager) {
            $this->assignPermissions($kitchenManager, [
                'kitchen.orders.read',
                'kitchen.orders.prepare',
                'kitchen.inventory.manage',
                'inventory.read',
                'inventory.update',
                'products.read',
            ]);
        }

        // Kitchen Staff
        $kitchenStaff = Role::where('slug', 'kitchen_staff')->first();
        if ($kitchenStaff) {
            $this->assignPermissions($kitchenStaff, [
                'kitchen.orders.read',
                'kitchen.orders.prepare',
                'inventory.read',
            ]);
        }

        // Driver
        $driver = Role::where('slug', 'driver')->first();
        if ($driver) {
            $this->assignPermissions($driver, [
                'deliveries.read',
                'deliveries.update',
                'orders.read',
            ]);
        }

        // Supplier Manager
        $supplierManager = Role::where('slug', 'supplier_manager')->first();
        if ($supplierManager) {
            $this->assignPermissions($supplierManager, [
                'suppliers.read',
                'suppliers.update',
                'products.read',
                'inventory.read',
                'orders.read',
            ]);
        }

        // Supplier Staff
        $supplierStaff = Role::where('slug', 'supplier_staff')->first();
        if ($supplierStaff) {
            $this->assignPermissions($supplierStaff, [
                'suppliers.read',
                'products.read',
                'inventory.read',
            ]);
        }

        // Customer (permissions minimales)
        $customer = Role::where('slug', 'customer')->first();
        if ($customer) {
            $this->assignPermissions($customer, [
                'orders.read',
                'orders.create',
                'products.read',
            ]);
        }
    }

    /**
     * Assigne des permissions à un rôle en utilisant des patterns
     *
     * @param Role $role
     * @param array $patterns Exemples: 'orders.*', 'products.read'
     */
    protected function assignPermissions(Role $role, array $patterns): void
    {
        $permissions = collect();

        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '.*')) {
                // Pattern wildcard: 'orders.*' -> toutes les permissions commençant par 'orders.'
                $prefix = str_replace('.*', '', $pattern);
                $matchingPerms = Permission::where('slug', 'like', $prefix . '%')->get();
                $permissions = $permissions->merge($matchingPerms);
            } else {
                // Permission exacte
                $perm = Permission::where('slug', $pattern)->first();
                if ($perm) {
                    $permissions->push($perm);
                }
            }
        }

        $role->permissions()->sync($permissions->pluck('id')->unique());
    }
}
