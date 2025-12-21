<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les groupes de permissions
        $groups = $this->createPermissionGroups();

        // Créer les permissions
        $this->createPermissions($groups);
    }

    /**
     * Créer les groupes de permissions
     */
    protected function createPermissionGroups(): array
    {
        $groupsData = [
            ['name' => 'Commandes', 'slug' => 'orders', 'description' => 'Gestion des commandes'],
            ['name' => 'Produits', 'slug' => 'products', 'description' => 'Gestion des produits'],
            ['name' => 'Inventaire', 'slug' => 'inventory', 'description' => 'Gestion des stocks'],
            ['name' => 'Cuisine', 'slug' => 'kitchen', 'description' => 'Opérations de cuisine'],
            ['name' => 'Livraisons', 'slug' => 'deliveries', 'description' => 'Gestion des livraisons'],
            ['name' => 'Analytics', 'slug' => 'analytics', 'description' => 'Rapports et analyses'],
            ['name' => 'Utilisateurs', 'slug' => 'users', 'description' => 'Gestion des utilisateurs'],
            ['name' => 'Paramètres', 'slug' => 'settings', 'description' => 'Configuration du système'],
            ['name' => 'Boutiques', 'slug' => 'shops', 'description' => 'Gestion des boutiques'],
            ['name' => 'Fournisseurs', 'slug' => 'suppliers', 'description' => 'Gestion des fournisseurs'],
        ];

        $groups = [];
        foreach ($groupsData as $groupData) {
            $groups[$groupData['slug']] = PermissionGroup::updateOrCreate(
                ['slug' => $groupData['slug']],
                $groupData
            );
        }

        return $groups;
    }

    /**
     * Créer les permissions
     */
    protected function createPermissions(array $groups): void
    {
        $permissions = [
            // Orders
            [
                'name' => 'Voir les commandes',
                'slug' => 'orders.read',
                'description' => 'Consulter la liste et détails des commandes',
                'group' => 'orders',
                'action_type' => 'read',
            ],
            [
                'name' => 'Créer des commandes',
                'slug' => 'orders.create',
                'description' => 'Créer de nouvelles commandes',
                'group' => 'orders',
                'action_type' => 'create',
            ],
            [
                'name' => 'Modifier les commandes',
                'slug' => 'orders.update',
                'description' => 'Mettre à jour les commandes existantes',
                'group' => 'orders',
                'action_type' => 'update',
            ],
            [
                'name' => 'Annuler les commandes',
                'slug' => 'orders.cancel',
                'description' => 'Annuler une commande',
                'group' => 'orders',
                'action_type' => 'delete',
            ],
            [
                'name' => 'Rembourser les commandes',
                'slug' => 'orders.refund',
                'description' => 'Effectuer un remboursement',
                'group' => 'orders',
                'action_type' => 'update',
            ],
            [
                'name' => 'Voir toutes les commandes',
                'slug' => 'orders.all.read',
                'description' => 'Voir les commandes de toutes les boutiques',
                'group' => 'orders',
                'action_type' => 'read',
            ],

            // Products
            [
                'name' => 'Voir les produits',
                'slug' => 'products.read',
                'description' => 'Consulter le catalogue produits',
                'group' => 'products',
                'action_type' => 'read',
            ],
            [
                'name' => 'Créer des produits',
                'slug' => 'products.create',
                'description' => 'Ajouter de nouveaux produits',
                'group' => 'products',
                'action_type' => 'create',
            ],
            [
                'name' => 'Modifier les produits',
                'slug' => 'products.update',
                'description' => 'Éditer les produits existants',
                'group' => 'products',
                'action_type' => 'update',
            ],
            [
                'name' => 'Supprimer les produits',
                'slug' => 'products.delete',
                'description' => 'Retirer des produits du catalogue',
                'group' => 'products',
                'action_type' => 'delete',
            ],
            [
                'name' => 'Modifier les prix',
                'slug' => 'products.pricing.update',
                'description' => 'Modifier les tarifs des produits',
                'group' => 'products',
                'action_type' => 'update',
            ],

            // Inventory
            [
                'name' => 'Voir l\'inventaire',
                'slug' => 'inventory.read',
                'description' => 'Consulter l\'état des stocks',
                'group' => 'inventory',
                'action_type' => 'read',
            ],
            [
                'name' => 'Mettre à jour l\'inventaire',
                'slug' => 'inventory.update',
                'description' => 'Modifier les niveaux de stock',
                'group' => 'inventory',
                'action_type' => 'update',
            ],
            [
                'name' => 'Réapprovisionner',
                'slug' => 'inventory.restock',
                'description' => 'Créer des demandes de réapprovisionnement',
                'group' => 'inventory',
                'action_type' => 'create',
            ],
            [
                'name' => 'Transférer du stock',
                'slug' => 'inventory.transfer',
                'description' => 'Transférer du stock entre boutiques',
                'group' => 'inventory',
                'action_type' => 'update',
            ],

            // Kitchen
            [
                'name' => 'Voir les commandes cuisine',
                'slug' => 'kitchen.orders.read',
                'description' => 'Consulter les commandes à préparer',
                'group' => 'kitchen',
                'action_type' => 'read',
            ],
            [
                'name' => 'Préparer les commandes',
                'slug' => 'kitchen.orders.prepare',
                'description' => 'Marquer les commandes comme préparées',
                'group' => 'kitchen',
                'action_type' => 'update',
            ],
            [
                'name' => 'Gérer l\'inventaire cuisine',
                'slug' => 'kitchen.inventory.manage',
                'description' => 'Gérer le stock de la cuisine',
                'group' => 'kitchen',
                'action_type' => 'update',
            ],

            // Deliveries
            [
                'name' => 'Voir les livraisons',
                'slug' => 'deliveries.read',
                'description' => 'Consulter les livraisons',
                'group' => 'deliveries',
                'action_type' => 'read',
            ],
            [
                'name' => 'Assigner les livraisons',
                'slug' => 'deliveries.assign',
                'description' => 'Assigner des livraisons aux chauffeurs',
                'group' => 'deliveries',
                'action_type' => 'update',
            ],
            [
                'name' => 'Mettre à jour les livraisons',
                'slug' => 'deliveries.update',
                'description' => 'Modifier le statut des livraisons',
                'group' => 'deliveries',
                'action_type' => 'update',
            ],

            // Analytics
            [
                'name' => 'Voir analytics boutique',
                'slug' => 'analytics.shop.read',
                'description' => 'Consulter les stats de sa boutique',
                'group' => 'analytics',
                'action_type' => 'read',
            ],
            [
                'name' => 'Voir analytics global',
                'slug' => 'analytics.all.read',
                'description' => 'Consulter les stats globales',
                'group' => 'analytics',
                'action_type' => 'read',
            ],
            [
                'name' => 'Exporter les rapports',
                'slug' => 'analytics.reports.export',
                'description' => 'Exporter les rapports en PDF/Excel',
                'group' => 'analytics',
                'action_type' => 'read',
            ],

            // Users
            [
                'name' => 'Voir les utilisateurs',
                'slug' => 'users.read',
                'description' => 'Consulter la liste des utilisateurs',
                'group' => 'users',
                'action_type' => 'read',
            ],
            [
                'name' => 'Créer des utilisateurs',
                'slug' => 'users.create',
                'description' => 'Ajouter de nouveaux utilisateurs',
                'group' => 'users',
                'action_type' => 'create',
            ],
            [
                'name' => 'Modifier les utilisateurs',
                'slug' => 'users.update',
                'description' => 'Éditer les utilisateurs',
                'group' => 'users',
                'action_type' => 'update',
            ],
            [
                'name' => 'Supprimer les utilisateurs',
                'slug' => 'users.delete',
                'description' => 'Désactiver des utilisateurs',
                'group' => 'users',
                'action_type' => 'delete',
            ],

            // Settings
            [
                'name' => 'Gérer les paramètres',
                'slug' => 'settings.manage',
                'description' => 'Modifier les paramètres système',
                'group' => 'settings',
                'action_type' => 'update',
            ],
            [
                'name' => 'Gérer les rôles',
                'slug' => 'settings.roles.manage',
                'description' => 'Créer et modifier les rôles',
                'group' => 'settings',
                'action_type' => 'update',
            ],
            [
                'name' => 'Gérer les permissions',
                'slug' => 'settings.permissions.manage',
                'description' => 'Attribuer des permissions',
                'group' => 'settings',
                'action_type' => 'update',
            ],

            // Shops
            [
                'name' => 'Voir les boutiques',
                'slug' => 'shops.read',
                'description' => 'Consulter les boutiques',
                'group' => 'shops',
                'action_type' => 'read',
            ],
            [
                'name' => 'Créer des boutiques',
                'slug' => 'shops.create',
                'description' => 'Ajouter de nouvelles boutiques',
                'group' => 'shops',
                'action_type' => 'create',
            ],
            [
                'name' => 'Modifier les boutiques',
                'slug' => 'shops.update',
                'description' => 'Éditer les boutiques',
                'group' => 'shops',
                'action_type' => 'update',
            ],

            // Suppliers
            [
                'name' => 'Voir les fournisseurs',
                'slug' => 'suppliers.read',
                'description' => 'Consulter les fournisseurs',
                'group' => 'suppliers',
                'action_type' => 'read',
            ],
            [
                'name' => 'Créer des fournisseurs',
                'slug' => 'suppliers.create',
                'description' => 'Ajouter de nouveaux fournisseurs',
                'group' => 'suppliers',
                'action_type' => 'create',
            ],
            [
                'name' => 'Modifier les fournisseurs',
                'slug' => 'suppliers.update',
                'description' => 'Éditer les fournisseurs',
                'group' => 'suppliers',
                'action_type' => 'update',
            ],
        ];

        foreach ($permissions as $permData) {
            $groupSlug = $permData['group'];
            unset($permData['group']);

            Permission::updateOrCreate(
                ['slug' => $permData['slug']],
                array_merge($permData, [
                    'permission_group_id' => $groups[$groupSlug]->id,
                    'group_name' => $groupSlug,
                    'active' => true,
                    'is_system' => false,
                ])
            );
        }
    }
}
