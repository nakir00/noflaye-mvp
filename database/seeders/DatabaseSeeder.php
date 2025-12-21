<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'description' => 'Full system access',
            'level' => 100,
            'is_system' => true,
        ]);

        $shopOwner = Role::create([
            'name' => 'Shop Owner',
            'slug' => 'shop_owner',
            'description' => 'Shop management access',
            'level' => 50,
        ]);

        $supplierOwner = Role::create([
            'name' => 'Supplier Owner',
            'slug' => 'supplier_owner',
            'description' => 'Supplier management access',
            'level' => 50,
        ]);

        $driver = Role::create([
            'name' => 'Driver',
            'slug' => 'driver',
            'description' => 'Delivery driver access',
            'level' => 20,
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@noflaye.sn',
            'password' => Hash::make('password'),
            'primary_role_id' => $superAdmin->id,
        ]);

        // Create test shops
        $shop1 = Shop::create([
            'name' => 'Yassa House',
            'slug' => 'yassa-house',
            'description' => 'Spécialités sénégalaises',
            'phone' => '+221 77 123 45 67',
            'email' => 'contact@yassahouse.sn',
            'address' => 'Dakar, Senegal',
            'is_active' => true,
        ]);

        $shop2 = Shop::create([
            'name' => 'Thiebou Délice',
            'slug' => 'thiebou-delice',
            'description' => 'Restaurant traditionnel',
            'phone' => '+221 77 234 56 78',
            'email' => 'info@thieboudelice.sn',
            'address' => 'Dakar, Senegal',
            'is_active' => true,
        ]);

        // Create shop owner user
        $shopUser = User::create([
            'name' => 'Amadou Diop',
            'email' => 'shop@noflaye.sn',
            'password' => Hash::make('password'),
            'primary_role_id' => $shopOwner->id,
        ]);

        $shopUser->shops()->attach([$shop1->id, $shop2->id]);

        // Create supplier
        $supplier = Supplier::create([
            'name' => 'Marché Central',
            'slug' => 'marche-central',
            'description' => 'Fournisseur de produits frais',
            'phone' => '+221 77 345 67 89',
            'email' => 'contact@marchecentral.sn',
            'address' => 'Dakar, Senegal',
            'is_active' => true,
        ]);

        // Create supplier owner user
        $supplierUser = User::create([
            'name' => 'Fatou Sall',
            'email' => 'supplier@noflaye.sn',
            'password' => Hash::make('password'),
            'primary_role_id' => $supplierOwner->id,
        ]);

        $supplierUser->suppliers()->attach($supplier->id);

        // Create driver user
        User::create([
            'name' => 'Mamadou Kane',
            'email' => 'driver@noflaye.sn',
            'password' => Hash::make('password'),
            'primary_role_id' => $driver->id,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@noflaye.sn / password');
        $this->command->info('Shop: shop@noflaye.sn / password');
        $this->command->info('Supplier: supplier@noflaye.sn / password');
        $this->command->info('Driver: driver@noflaye.sn / password');
    }
}
