<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\PermissionTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔐 Seeding Permission System...');
        $this->command->newLine();

        // Disable observers temporarily to avoid wildcard issues during seeding
        PermissionTemplate::unsetEventDispatcher();

        // 1. Create Permission Groups
        $groups = $this->createPermissionGroups();

        // 2. Create Permissions from Enum
        $this->createPermissionsFromEnum($groups);

        // 3. Create Permission Templates
        $this->createPermissionTemplates();

        // 4. Assign Permissions to Templates
        $this->assignPermissionsToTemplates();

        $this->command->newLine();
        $this->command->info('✅ Permission System seeded successfully!');
    }

    /**
     * Create permission groups
     */
    protected function createPermissionGroups(): array
    {
        $this->command->info('📁 Creating permission groups...');

        $groupsData = [
            [
                'name' => 'User Management',
                'slug' => 'user-management',
                'description' => 'Manage users, roles, and access',
            ],
            [
                'name' => 'Shop Management',
                'slug' => 'shop-management',
                'description' => 'Manage shops and locations',
            ],
            [
                'name' => 'Kitchen Management',
                'slug' => 'kitchen-management',
                'description' => 'Manage kitchens and operations',
            ],
            [
                'name' => 'Driver Management',
                'slug' => 'driver-management',
                'description' => 'Manage drivers and deliveries',
            ],
            [
                'name' => 'Supervisor Management',
                'slug' => 'supervisor-management',
                'description' => 'Manage supervisors and oversight',
            ],
            [
                'name' => 'Supplier Management',
                'slug' => 'supplier-management',
                'description' => 'Manage suppliers and inventory',
            ],
            [
                'name' => 'Permission Management',
                'slug' => 'permission-management',
                'description' => 'Manage permissions and templates',
            ],
            [
                'name' => 'System',
                'slug' => 'system',
                'description' => 'System-level permissions',
            ],
        ];

        $groups = [];
        foreach ($groupsData as $data) {
            $group = PermissionGroup::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $groups[$data['slug']] = $group;
        }

        $this->command->info("   Created {$this->count($groups)} permission groups");

        return $groups;
    }

    /**
     * Create permissions from enum
     */
    protected function createPermissionsFromEnum(array $groups): void
    {
        $this->command->info('🔑 Creating permissions from enum...');

        $created = 0;
        $skipped = 0;

        foreach (PermissionEnum::cases() as $permissionCase) {
            $slug = $permissionCase->value;

            // Determine group based on permission slug prefix
            $group = $this->determineGroupFromSlug($slug, $groups);

            // Extract action type from slug (last part after last dot)
            $parts = explode('.', $slug);
            $actionType = end($parts);

            // Generate name from slug
            $name = $this->generateNameFromSlug($slug);

            $permissionData = [
                'name' => $name,
                'slug' => $slug,
                'description' => "Permission for {$slug}",
                'permission_group_id' => $group?->id,
                'group_name' => $group?->slug ?? 'system',
                'action_type' => $actionType,
                'active' => true,
                'is_system' => in_array($slug, [
                    'wildcards.admin',
                    'wildcards.all',
                    'wildcards.own',
                    'audit.view',
                    'audit.export',
                ]),
            ];

            $exists = Permission::where('slug', $slug)->exists();

            if (! $exists) {
                Permission::create($permissionData);
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("   Created {$created} permissions, skipped {$skipped}");
    }

    /**
     * Determine group from permission slug
     */
    protected function determineGroupFromSlug(string $slug, array $groups): ?PermissionGroup
    {
        $slugLower = strtolower($slug);

        if (str_starts_with($slugLower, 'users.')) {
            return $groups['user-management'] ?? null;
        }
        if (str_starts_with($slugLower, 'shops.')) {
            return $groups['shop-management'] ?? null;
        }
        if (str_starts_with($slugLower, 'kitchens.')) {
            return $groups['kitchen-management'] ?? null;
        }
        if (str_starts_with($slugLower, 'drivers.')) {
            return $groups['driver-management'] ?? null;
        }
        if (str_starts_with($slugLower, 'supervisors.')) {
            return $groups['supervisor-management'] ?? null;
        }
        if (str_starts_with($slugLower, 'suppliers.')) {
            return $groups['supplier-management'] ?? null;
        }
        if (str_starts_with($slugLower, 'permissions.') || str_starts_with($slugLower, 'templates.') || str_starts_with($slugLower, 'delegations.') || str_starts_with($slugLower, 'requests.')) {
            return $groups['permission-management'] ?? null;
        }

        return $groups['system'] ?? null;
    }

    /**
     * Generate human-readable name from slug
     */
    protected function generateNameFromSlug(string $slug): string
    {
        $parts = explode('.', $slug);
        $formatted = array_map(fn ($part) => Str::title(str_replace('_', ' ', $part)), $parts);

        return implode(' - ', $formatted);
    }

    /**
     * Create permission templates
     */
    protected function createPermissionTemplates(): void
    {
        $this->command->info('📋 Creating permission templates...');

        $templates = [
            [
                'name' => 'Super Administrator',
                'slug' => 'super-admin',
                'description' => 'Full system access with all permissions',
                'color' => '#dc2626',
                'icon' => 'heroicon-o-shield-exclamation',
                'level' => 0,
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Administrative access to most features',
                'color' => '#ef4444',
                'icon' => 'heroicon-o-shield-check',
                'level' => 1,
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Shop Manager',
                'slug' => 'shop-manager',
                'description' => 'Manage shop operations and staff',
                'color' => '#10b981',
                'icon' => 'heroicon-o-building-storefront',
                'level' => 2,
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Shop Staff',
                'slug' => 'shop-staff',
                'description' => 'Work in shop with limited access',
                'color' => '#059669',
                'icon' => 'heroicon-o-user-circle',
                'level' => 3,
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Kitchen Manager',
                'slug' => 'kitchen-manager',
                'description' => 'Manage kitchen operations',
                'color' => '#f59e0b',
                'icon' => 'heroicon-o-fire',
                'level' => 2,
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Kitchen Staff',
                'slug' => 'kitchen-staff',
                'description' => 'Work in kitchen with limited access',
                'color' => '#d97706',
                'icon' => 'heroicon-o-user-circle',
                'level' => 3,
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Driver',
                'slug' => 'driver',
                'description' => 'Deliver orders',
                'color' => '#8b5cf6',
                'icon' => 'heroicon-o-truck',
                'level' => 3,
                'sort_order' => 7,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Supervisor',
                'slug' => 'supervisor',
                'description' => 'Supervise operations across locations',
                'color' => '#06b6d4',
                'icon' => 'heroicon-o-eye',
                'level' => 2,
                'sort_order' => 8,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Supplier Manager',
                'slug' => 'supplier-manager',
                'description' => 'Manage supplier relationships',
                'color' => '#ec4899',
                'icon' => 'heroicon-o-cube',
                'level' => 2,
                'sort_order' => 9,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => true,
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Customer access to place orders',
                'color' => '#3b82f6',
                'icon' => 'heroicon-o-user',
                'level' => 4,
                'sort_order' => 10,
                'is_active' => true,
                'is_system' => false,
                'auto_sync_users' => false,
            ],
        ];

        foreach ($templates as $data) {
            PermissionTemplate::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info("   Created {$this->count($templates)} templates");
    }

    /**
     * Assign permissions to templates
     */
    protected function assignPermissionsToTemplates(): void
    {
        $this->command->info('🔗 Assigning permissions to templates...');

        // Super Admin - ALL permissions
        $superAdmin = PermissionTemplate::where('slug', 'super-admin')->first();
        $allPermissions = Permission::all()->pluck('id')->toArray();
        $superAdmin->permissions()->sync($this->mapPermissions($allPermissions));
        $this->command->info("   Super Administrator: {$this->count($allPermissions)} permissions");

        // Admin - Most permissions except super admin wildcards
        $admin = PermissionTemplate::where('slug', 'admin')->first();
        $adminPermissions = Permission::whereNotIn('slug', [
            'wildcards.admin',
            'wildcards.all',
        ])->pluck('id')->toArray();
        $admin->permissions()->sync($this->mapPermissions($adminPermissions));
        $this->command->info("   Administrator: {$this->count($adminPermissions)} permissions");

        // Shop Manager
        $shopManager = PermissionTemplate::where('slug', 'shop-manager')->first();
        $shopManagerPerms = Permission::whereIn('slug', [
            'shops.viewAny',
            'shops.view',
            'shops.update',
            'users.viewAny',
            'users.view',
            'users.update',
        ])->pluck('id')->toArray();
        $shopManager->permissions()->sync($this->mapPermissions($shopManagerPerms));
        $this->command->info("   Shop Manager: {$this->count($shopManagerPerms)} permissions");

        // Shop Staff
        $shopStaff = PermissionTemplate::where('slug', 'shop-staff')->first();
        $shopStaffPerms = Permission::whereIn('slug', [
            'shops.view',
        ])->pluck('id')->toArray();
        $shopStaff->permissions()->sync($this->mapPermissions($shopStaffPerms));
        $this->command->info("   Shop Staff: {$this->count($shopStaffPerms)} permissions");

        // Kitchen Manager
        $kitchenManager = PermissionTemplate::where('slug', 'kitchen-manager')->first();
        $kitchenManagerPerms = Permission::whereIn('slug', [
            'kitchens.viewAny',
            'kitchens.view',
            'kitchens.update',
            'users.viewAny',
            'users.view',
        ])->pluck('id')->toArray();
        $kitchenManager->permissions()->sync($this->mapPermissions($kitchenManagerPerms));
        $this->command->info("   Kitchen Manager: {$this->count($kitchenManagerPerms)} permissions");

        // Kitchen Staff
        $kitchenStaff = PermissionTemplate::where('slug', 'kitchen-staff')->first();
        $kitchenStaffPerms = Permission::whereIn('slug', [
            'kitchens.view',
        ])->pluck('id')->toArray();
        $kitchenStaff->permissions()->sync($this->mapPermissions($kitchenStaffPerms));
        $this->command->info("   Kitchen Staff: {$this->count($kitchenStaffPerms)} permissions");

        // Driver
        $driver = PermissionTemplate::where('slug', 'driver')->first();
        $driverPerms = Permission::whereIn('slug', [
            'drivers.view',
            'drivers.update',
        ])->pluck('id')->toArray();
        $driver->permissions()->sync($this->mapPermissions($driverPerms));
        $this->command->info("   Driver: {$this->count($driverPerms)} permissions");

        // Supervisor
        $supervisor = PermissionTemplate::where('slug', 'supervisor')->first();
        $supervisorPerms = Permission::whereIn('slug', [
            'shops.viewAny',
            'shops.view',
            'kitchens.viewAny',
            'kitchens.view',
            'drivers.viewAny',
            'drivers.view',
            'drivers.assign',
            'supervisors.view',
        ])->pluck('id')->toArray();
        $supervisor->permissions()->sync($this->mapPermissions($supervisorPerms));
        $this->command->info("   Supervisor: {$this->count($supervisorPerms)} permissions");

        // Supplier Manager
        $supplierManager = PermissionTemplate::where('slug', 'supplier-manager')->first();
        $supplierManagerPerms = Permission::whereIn('slug', [
            'suppliers.viewAny',
            'suppliers.view',
            'suppliers.update',
            'suppliers.manage',
        ])->pluck('id')->toArray();
        $supplierManager->permissions()->sync($this->mapPermissions($supplierManagerPerms));
        $this->command->info("   Supplier Manager: {$this->count($supplierManagerPerms)} permissions");

        // Customer - No permissions
        $customer = PermissionTemplate::where('slug', 'customer')->first();
        $customer->permissions()->sync([]);
        $this->command->info('   Customer: 0 permissions');
    }

    /**
     * Map permissions for sync
     */
    protected function mapPermissions(array $permissionIds): array
    {
        return collect($permissionIds)->mapWithKeys(fn ($id, $index) => [
            $id => [
                'source' => 'direct',
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ])->toArray();
    }

    /**
     * Count helper
     */
    protected function count($array): int
    {
        return is_array($array) ? count($array) : (is_countable($array) ? count($array) : 0);
    }
}
