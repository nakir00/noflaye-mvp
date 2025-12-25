<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            // Administrateurs
            [
                'name' => 'Super Administrateur',
                'slug' => 'super_admin',
                'description' => 'Accès complet au système, gestion globale',
                'level' => 100,
                'active' => true,
                'is_system' => true,
                'color' => 'danger',
            ],
            [
                'name' => 'Administrateur',
                'slug' => 'admin',
                'description' => 'Gestion administrative complète sauf configurations critiques',
                'level' => 90,
                'active' => true,
                'is_system' => true,
                'color' => 'danger',
            ],

            // Gestionnaires de boutique
            [
                'name' => 'Manager Boutique Senior',
                'slug' => 'shop_manager_senior',
                'description' => 'Gestion complète de boutique avec validation financière élevée',
                'level' => 83,
                'active' => true,
                'is_system' => false,
                'color' => 'primary',
            ],
            [
                'name' => 'Manager Boutique',
                'slug' => 'shop_manager',
                'description' => 'Gestion quotidienne de boutique',
                'level' => 82,
                'active' => true,
                'is_system' => false,
                'color' => 'primary',
            ],
            [
                'name' => 'Manager Boutique Junior',
                'slug' => 'shop_manager_junior',
                'description' => 'Assistant manager avec permissions limitées',
                'level' => 81,
                'active' => true,
                'is_system' => false,
                'color' => 'primary',
            ],
            [
                'name' => 'Manager Boutique Stagiaire',
                'slug' => 'shop_manager_trainee',
                'description' => 'Manager en formation avec permissions restreintes',
                'level' => 80,
                'active' => true,
                'is_system' => false,
                'color' => 'primary',
            ],

            // Cuisine
            [
                'name' => 'Manager Cuisine',
                'slug' => 'kitchen_manager',
                'description' => 'Responsable de cuisine, gestion des commandes et stock',
                'level' => 75,
                'active' => true,
                'is_system' => false,
                'color' => 'orange',
            ],
            [
                'name' => 'Staff Cuisine',
                'slug' => 'kitchen_staff',
                'description' => 'Employé de cuisine, préparation des commandes',
                'level' => 60,
                'active' => true,
                'is_system' => false,
                'color' => 'orange',
            ],

            // Livraison
            [
                'name' => 'Manager Livreur',
                'slug' => 'driver_manager',
                'description' => 'Gestion des livreurs et opérations de livraison',
                'level' => 70,
                'active' => true,
                'is_system' => false,
                'color' => 'green',
            ],
            [
                'name' => 'Chauffeur Livreur',
                'slug' => 'driver',
                'description' => 'Livraison des commandes aux clients',
                'level' => 40,
                'active' => true,
                'is_system' => false,
                'color' => 'green',
            ],

            // Superviseurs
            [
                'name' => 'Manager Superviseur',
                'slug' => 'supervisor_manager',
                'description' => 'Superviseur régional gérant plusieurs entités',
                'level' => 85,
                'active' => true,
                'is_system' => false,
                'color' => 'purple',
            ],
            [
                'name' => 'Staff Superviseur',
                'slug' => 'supervisor_staff',
                'description' => 'Membre du staff de supervision',
                'level' => 70,
                'active' => true,
                'is_system' => false,
                'color' => 'purple',
            ],

            // Fournisseurs
            [
                'name' => 'Manager Fournisseur',
                'slug' => 'supplier_manager',
                'description' => 'Gestionnaire de compte fournisseur',
                'level' => 55,
                'active' => true,
                'is_system' => false,
                'color' => 'info',
            ],
            [
                'name' => 'Staff Fournisseur',
                'slug' => 'supplier_staff',
                'description' => 'Employé fournisseur',
                'level' => 53,
                'active' => true,
                'is_system' => false,
                'color' => 'info',
            ],

            // Support
            [
                'name' => 'Manager Support',
                'slug' => 'support_manager',
                'description' => 'Responsable support client',
                'level' => 53,
                'active' => true,
                'is_system' => false,
                'color' => 'secondary',
            ],
            [
                'name' => 'Support Niveau 2',
                'slug' => 'support_tier_2',
                'description' => 'Agent support avancé',
                'level' => 52,
                'active' => true,
                'is_system' => false,
                'color' => 'secondary',
            ],
            [
                'name' => 'Support Niveau 1',
                'slug' => 'support_tier_1',
                'description' => 'Agent support de base',
                'level' => 51,
                'active' => true,
                'is_system' => false,
                'color' => 'secondary',
            ],

            // Partenaires & Clients
            [
                'name' => 'Partenaire',
                'slug' => 'partner',
                'description' => 'Partenaire commercial',
                'level' => 50,
                'active' => true,
                'is_system' => false,
                'color' => 'gray',
            ],
            [
                'name' => 'Client VIP',
                'slug' => 'vip_customer',
                'description' => 'Client avec avantages premium',
                'level' => 10,
                'active' => true,
                'is_system' => false,
                'color' => 'yellow',
            ],
            [
                'name' => 'Client',
                'slug' => 'customer',
                'description' => 'Client standard',
                'level' => 1,
                'active' => true,
                'is_system' => false,
                'color' => 'gray',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('✅ Roles created/updated successfully');
    }
}
