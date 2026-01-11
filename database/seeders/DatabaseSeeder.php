<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Database Seeding...');
        $this->command->newLine();

        // Check if --force flag is provided
        $force = $this->command->option('force');

        if ($force) {
            $this->seedAll();
        } else {
            // Interactive mode
            $seedAll = $this->command->confirm(
                'Seed everything (permissions + demo data)?',
                default: true
            );

            if ($seedAll) {
                $this->seedAll();
            } else {
                $this->seedSelectively();
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed!');
    }

    /**
     * Seed all data
     */
    protected function seedAll(): void
    {
        $this->command->info('📦 Seeding all data...');
        $this->command->newLine();

        $this->call(PermissionSystemSeeder::class);
        $this->call(DemoDataSeeder::class);

        $this->displayLoginCredentials();
    }

    /**
     * Seed selectively based on user choices
     */
    protected function seedSelectively(): void
    {
        $seedPermissions = $this->command->confirm(
            'Seed permission system?',
            default: true
        );

        if ($seedPermissions) {
            $this->call(PermissionSystemSeeder::class);
        }

        $seedDemoData = $this->command->confirm(
            'Seed demo data (shops, kitchens, users)?',
            default: false
        );

        if ($seedDemoData) {
            $this->call(DemoDataSeeder::class);
            $this->displayLoginCredentials();
        }
    }

    /**
     * Display login credentials
     */
    protected function displayLoginCredentials(): void
    {
        $this->command->newLine();
        $this->command->info('🔐 LOGIN CREDENTIALS');
        $this->command->info('===================');
        $this->command->newLine();

        $this->command->table(
            ['Role', 'Email', 'Password', 'Panel(s)'],
            [
                ['Super Admin', 'super@noflaye.com', 'password', 'Admin'],
                ['Admin', 'admin@noflaye.com', 'password', 'Admin'],
                ['Shop Manager', 'alice@noflaye.com', 'password', 'Shop'],
                ['Kitchen Manager', 'bob@noflaye.com', 'password', 'Kitchen'],
                ['Manager + Driver', 'charlie@noflaye.com', 'password', 'Shop, Driver'],
                ['Staff + Driver', 'diana@noflaye.com', 'password', 'Kitchen, Driver'],
                ['Supervisor', 'eve@noflaye.com', 'password', 'Supervisor'],
                ['Multi-Shop Staff', 'frank@noflaye.com', 'password', 'Shop, Driver'],
                ['Driver', 'grace@noflaye.com', 'password', 'Driver'],
                ['Customer', 'customer@noflaye.com', 'password', 'Customer'],
            ]
        );

        $this->command->newLine();
        $this->command->warn('⚠️  Default password for all accounts: password');
        $this->command->warn('⚠️  Please change passwords in production!');
        $this->command->newLine();

        $this->command->info('🔗 Access panels at:');
        $this->command->info('   Admin: http://localhost/admin');
        $this->command->info('   Shop: http://localhost/shop');
        $this->command->info('   Kitchen: http://localhost/kitchen');
        $this->command->info('   Driver: http://localhost/driver');
        $this->command->info('   Supervisor: http://localhost/supervisor');
    }
}
