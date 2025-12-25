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
