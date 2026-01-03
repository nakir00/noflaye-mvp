<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Kitchen;
use App\Models\Role;
use App\Models\Shop;
use App\Models\Supervisor;
use App\Models\User;
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
