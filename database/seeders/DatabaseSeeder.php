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
            PanelConfigurationSeeder::class,
            MultiPanelUserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('');
    }
}
