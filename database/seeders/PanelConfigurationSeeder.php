<?php

namespace Database\Seeders;

use App\Models\PanelConfiguration;
use Illuminate\Database\Seeder;

class PanelConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            [
                'panel_id' => 'admin',
                'can_manage_users' => true,
                'can_manage_roles' => true,
                'can_manage_permissions' => true,
                'can_invite_users' => true,
                'can_assign_managers' => true,
                'can_create_templates' => true,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'shop',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => true,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'kitchen',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => true,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'driver',
                'can_manage_users' => false,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => false,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => false,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'supplier',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => false,
                'can_invite_users' => true,
                'can_assign_managers' => false,
                'can_create_templates' => false,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
            [
                'panel_id' => 'supervisor',
                'can_manage_users' => true,
                'can_manage_roles' => false,
                'can_manage_permissions' => true,
                'can_invite_users' => true,
                'can_assign_managers' => true,
                'can_create_templates' => true,
                'can_assign_templates' => true,
                'can_view_own_permissions' => true,
            ],
        ];

        foreach ($configurations as $config) {
            PanelConfiguration::updateOrCreate(
                ['panel_id' => $config['panel_id']],
                $config
            );
        }

        $this->command->info('✅ Panel configurations created successfully');
    }
}
