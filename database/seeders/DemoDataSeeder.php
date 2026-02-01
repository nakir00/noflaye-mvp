<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Kitchen;
use App\Models\PermissionTemplate;
use App\Models\Shop;
use App\Models\Supervisor;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎭 Seeding Demo Data...');
        $this->command->newLine();

        // 1. Create Shops
        $shops = $this->createShops();

        // 2. Create Kitchens
        $kitchens = $this->createKitchens();

        // 3. Create Suppliers
        $suppliers = $this->createSuppliers();

        // 4. Create Users with Templates
        $users = $this->createUsers();

        // 5. Assign Users to Entities (shops, kitchens, drivers)
        $this->assignUsersToEntities($users, $shops, $kitchens);

        $this->command->newLine();
        $this->command->info('✅ Demo data seeded successfully!');
    }

    /**
     * Create shops (8 shops for testing)
     */
    protected function createShops(): array
    {
        $this->command->info('🏪 Creating shops...');

        $shopsData = [
            [
                'name' => 'Noflaye Downtown',
                'slug' => 'noflaye-downtown',
                'description' => 'Main downtown location',
                'address' => '123 Main Street, Downtown',
                'phone' => '+221771234567',
                'email' => 'downtown@noflaye.com',
                'is_active' => true,
            ],
            [
                'name' => 'Noflaye Plateau',
                'slug' => 'noflaye-plateau',
                'description' => 'Plateau business district location',
                'address' => '456 Business Avenue, Plateau',
                'phone' => '+221771234568',
                'email' => 'plateau@noflaye.com',
                'is_active' => true,
            ],
            [
                'name' => 'Noflaye Almadies',
                'slug' => 'noflaye-almadies',
                'description' => 'Beachside location',
                'address' => '789 Beach Road, Almadies',
                'phone' => '+221771234569',
                'email' => 'almadies@noflaye.com',
                'is_active' => true,
            ],
            [
                'name' => 'Perfect Shoes',
                'slug' => 'perfect-shoes',
                'description' => 'Premium shoe store',
                'address' => '100 Fashion Street, Dakar',
                'phone' => '+221771234580',
                'email' => 'contact@perfectshoes.sn',
                'is_active' => true,
            ],
            [
                'name' => 'Almamy Bijouterie',
                'slug' => 'almamy-bijouterie',
                'description' => 'Fine jewelry store',
                'address' => '200 Luxury Avenue, Plateau',
                'phone' => '+221771234581',
                'email' => 'contact@almamy-bijouterie.sn',
                'is_active' => true,
            ],
            [
                'name' => 'Tech Store Dakar',
                'slug' => 'tech-store-dakar',
                'description' => 'Electronics and gadgets',
                'address' => '300 Tech Park, Dakar',
                'phone' => '+221771234582',
                'email' => 'sales@techstore.sn',
                'is_active' => true,
            ],
            [
                'name' => 'Mode Fashion',
                'slug' => 'mode-fashion',
                'description' => 'Trendy fashion boutique',
                'address' => '400 Style Street, Almadies',
                'phone' => '+221771234583',
                'email' => 'info@modefashion.sn',
                'is_active' => true,
            ],
            [
                'name' => 'Sénégal Artisanat',
                'slug' => 'senegal-artisanat',
                'description' => 'Local crafts and arts',
                'address' => '500 Culture Lane, Gorée',
                'phone' => '+221771234584',
                'email' => 'contact@senegal-artisanat.sn',
                'is_active' => true,
            ],
        ];

        $shops = [];
        foreach ($shopsData as $data) {
            $shop = Shop::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $shops[$data['slug']] = $shop;
            $this->command->info("   ✓ {$shop->name}");
        }

        return $shops;
    }

    /**
     * Create kitchens
     */
    protected function createKitchens(): array
    {
        $this->command->info('🍳 Creating kitchens...');

        $kitchensData = [
            [
                'name' => 'Central Kitchen',
                'slug' => 'central-kitchen',
                'description' => 'Main production kitchen',
                'address' => '100 Industrial Zone, Central',
                'phone' => '+221771234570',
                'email' => 'central@noflaye.com',
                'is_active' => true,
            ],
            [
                'name' => 'Downtown Kitchen',
                'slug' => 'downtown-kitchen',
                'description' => 'Downtown preparation facility',
                'address' => '125 Main Street, Downtown',
                'phone' => '+221771234571',
                'email' => 'kitchen.downtown@noflaye.com',
                'is_active' => true,
            ],
        ];

        $kitchens = [];
        foreach ($kitchensData as $data) {
            $kitchen = Kitchen::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $kitchens[$data['slug']] = $kitchen;
            $this->command->info("   ✓ {$kitchen->name}");
        }

        return $kitchens;
    }

    /**
     * Create suppliers
     */
    protected function createSuppliers(): array
    {
        $this->command->info('📦 Creating suppliers...');

        $suppliersData = [
            [
                'name' => 'Fresh Foods Supply',
                'slug' => 'fresh-foods-supply',
                'description' => 'Fresh ingredients supplier',
                'address' => '200 Market Street',
                'phone' => '+221771234572',
                'email' => 'contact@freshfoods.sn',
                'is_active' => true,
            ],
            [
                'name' => 'Packaging Solutions',
                'slug' => 'packaging-solutions',
                'description' => 'Packaging materials supplier',
                'address' => '300 Industrial Park',
                'phone' => '+221771234573',
                'email' => 'sales@packagingsolutions.sn',
                'is_active' => true,
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $data) {
            $supplier = Supplier::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $suppliers[$data['slug']] = $supplier;
            $this->command->info("   ✓ {$supplier->name}");
        }

        return $suppliers;
    }

    /**
     * Create users with templates
     */
    protected function createUsers(): array
    {
        $this->command->info('👥 Creating users...');

        $password = Hash::make('password');

        $usersData = [
            [
                'name' => 'Super Admin',
                'email' => 'super@noflaye.com',
                'password' => $password,
                'templates' => ['super-admin'],
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@noflaye.com',
                'password' => $password,
                'templates' => ['admin'],
            ],
            [
                'name' => 'Alice Manager',
                'email' => 'alice@noflaye.com',
                'password' => $password,
                'templates' => ['shop-manager'],
            ],
            [
                'name' => 'Bob Kitchen',
                'email' => 'bob@noflaye.com',
                'password' => $password,
                'templates' => ['kitchen-manager'],
            ],
            [
                'name' => 'Charlie Multi',
                'email' => 'charlie@noflaye.com',
                'password' => $password,
                'templates' => ['shop-manager', 'driver'],
            ],
            [
                'name' => 'Diana Worker',
                'email' => 'diana@noflaye.com',
                'password' => $password,
                'templates' => ['kitchen-staff', 'driver'],
            ],
            [
                'name' => 'Eve Supervisor',
                'email' => 'eve@noflaye.com',
                'password' => $password,
                'templates' => ['supervisor'],
            ],
            [
                'name' => 'Frank Flexible',
                'email' => 'frank@noflaye.com',
                'password' => $password,
                'templates' => ['shop-staff', 'driver'],
            ],
            [
                'name' => 'Grace Driver',
                'email' => 'grace@noflaye.com',
                'password' => $password,
                'templates' => ['driver'],
            ],
            [
                'name' => 'Customer Demo',
                'email' => 'customer@noflaye.com',
                'password' => $password,
                'templates' => ['customer'],
            ],
            // Multi-shop user for testing
            [
                'name' => 'Multi Shop Manager',
                'email' => 'multishop@noflaye.com',
                'password' => $password,
                'templates' => ['shop-manager', 'driver', 'supervisor'],
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $templateSlugs = $data['templates'];
            unset($data['templates']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Assign templates
            $templates = PermissionTemplate::whereIn('slug', $templateSlugs)->get();
            foreach ($templates as $template) {
                $user->assignTemplate($template);
            }

            $users[$data['email']] = $user;
            $this->command->info("   ✓ {$user->name} ({$user->email})");
        }

        return $users;
    }

    /**
     * Assign users to entities
     */
    protected function assignUsersToEntities(array $users, array $shops, array $kitchens): void
    {
        $this->command->info('🔗 Assigning users to entities...');

        // Alice Manager → Noflaye Downtown
        $alice = $users['alice@noflaye.com'];
        $alice->shops()->syncWithoutDetaching([$shops['noflaye-downtown']->id]);
        $this->command->info("   ✓ {$alice->name} → {$shops['noflaye-downtown']->name}");

        // Bob Kitchen → Central Kitchen
        $bob = $users['bob@noflaye.com'];
        $bob->kitchens()->syncWithoutDetaching([$kitchens['central-kitchen']->id]);
        $this->command->info("   ✓ {$bob->name} → {$kitchens['central-kitchen']->name}");

        // Charlie Multi → Noflaye Plateau + Driver
        $charlie = $users['charlie@noflaye.com'];
        $charlie->shops()->syncWithoutDetaching([$shops['noflaye-plateau']->id]);
        $charlieDriver = Driver::updateOrCreate(
            ['slug' => 'charlie-multi-driver'],
            [
                'user_id' => $charlie->id,
                'name' => $charlie->name,
                'vehicle_type' => 'Motorcycle',
                'vehicle_number' => 'DRV-001',
                'license_number' => 'LIC-001',
                'is_active' => true,
                'is_available' => true,
            ]
        );
        $charlie->drivers()->syncWithoutDetaching([$charlieDriver->id]);
        $this->command->info("   ✓ {$charlie->name} → {$shops['noflaye-plateau']->name} + Driver (Motorcycle)");

        // Diana Worker → Downtown Kitchen + Driver
        $diana = $users['diana@noflaye.com'];
        $diana->kitchens()->syncWithoutDetaching([$kitchens['downtown-kitchen']->id]);
        $dianaDriver = Driver::updateOrCreate(
            ['slug' => 'diana-worker-driver'],
            [
                'user_id' => $diana->id,
                'name' => $diana->name,
                'vehicle_type' => 'Scooter',
                'vehicle_number' => 'DRV-002',
                'license_number' => 'LIC-002',
                'is_active' => true,
                'is_available' => true,
            ]
        );
        $diana->drivers()->syncWithoutDetaching([$dianaDriver->id]);
        $this->command->info("   ✓ {$diana->name} → {$kitchens['downtown-kitchen']->name} + Driver (Scooter)");

        // Eve Supervisor → Supervisor profile
        $eve = $users['eve@noflaye.com'];
        $eveSupervisor = Supervisor::updateOrCreate(
            ['slug' => 'eve-supervisor'],
            [
                'user_id' => $eve->id,
                'name' => $eve->name,
                'is_active' => true,
            ]
        );
        $eve->supervisors()->syncWithoutDetaching([$eveSupervisor->id]);
        $this->command->info("   ✓ {$eve->name} → Supervisor");

        // Frank Flexible → Downtown + Almadies shops + Driver
        $frank = $users['frank@noflaye.com'];
        $frank->shops()->syncWithoutDetaching([
            $shops['noflaye-downtown']->id,
            $shops['noflaye-almadies']->id,
        ]);
        $frankDriver = Driver::updateOrCreate(
            ['slug' => 'frank-flexible-driver'],
            [
                'user_id' => $frank->id,
                'name' => $frank->name,
                'vehicle_type' => 'Car',
                'vehicle_number' => 'DRV-003',
                'license_number' => 'LIC-003',
                'is_active' => true,
                'is_available' => true,
            ]
        );
        $frank->drivers()->syncWithoutDetaching([$frankDriver->id]);
        $this->command->info("   ✓ {$frank->name} → Multi-Shop (Downtown + Almadies) + Driver (Car)");

        // Grace Driver → Pure driver
        $grace = $users['grace@noflaye.com'];
        $graceDriver = Driver::updateOrCreate(
            ['slug' => 'grace-driver'],
            [
                'user_id' => $grace->id,
                'name' => $grace->name,
                'vehicle_type' => 'Motorcycle',
                'vehicle_number' => 'DRV-004',
                'license_number' => 'LIC-004',
                'is_active' => true,
                'is_available' => true,
            ]
        );
        $grace->drivers()->syncWithoutDetaching([$graceDriver->id]);
        $this->command->info("   ✓ {$grace->name} → Pure Driver (Motorcycle)");

        // Multi Shop Manager → 4 shops + 2 drivers + 1 supervisor
        $multiShop = $users['multishop@noflaye.com'];
        $multiShop->shops()->syncWithoutDetaching([
            $shops['perfect-shoes']->id,
            $shops['almamy-bijouterie']->id,
            $shops['tech-store-dakar']->id,
            $shops['mode-fashion']->id,
        ]);

        // Create driver for Perfect Shoes
        $multiDriver1 = Driver::updateOrCreate(
            ['slug' => 'multishop-driver-perfectshoes'],
            [
                'user_id' => $multiShop->id,
                'name' => 'Driver Perfect Shoes',
                'vehicle_type' => 'Van',
                'vehicle_number' => 'DRV-MS1',
                'license_number' => 'LIC-MS1',
                'is_active' => true,
                'is_available' => true,
            ]
        );
        $multiShop->drivers()->syncWithoutDetaching([$multiDriver1->id]);

        // Create driver for Almamy
        $multiDriver2 = Driver::updateOrCreate(
            ['slug' => 'multishop-driver-almamy'],
            [
                'user_id' => $multiShop->id,
                'name' => 'Driver Almamy Bijouterie',
                'vehicle_type' => 'Motorcycle',
                'vehicle_number' => 'DRV-MS2',
                'license_number' => 'LIC-MS2',
                'is_active' => true,
                'is_available' => true,
            ]
        );
        $multiShop->drivers()->syncWithoutDetaching([$multiDriver2->id]);

        // Create supervisor for Almamy
        $multiSupervisor = Supervisor::updateOrCreate(
            ['slug' => 'multishop-supervisor-almamy'],
            [
                'user_id' => $multiShop->id,
                'name' => 'Superviseur Almamy Bijouterie',
                'is_active' => true,
            ]
        );
        $multiShop->supervisors()->syncWithoutDetaching([$multiSupervisor->id]);

        $this->command->info("   ✓ {$multiShop->name} → 4 Shops + 2 Drivers + 1 Supervisor");
    }
}
