<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'panel_id',
        'can_manage_users',
        'can_manage_roles',
        'can_manage_permissions',
        'can_invite_users',
        'can_assign_managers',
        'can_create_templates',
        'can_assign_templates',
        'can_view_own_permissions',
        'additional_settings',
    ];

    protected function casts(): array
    {
        return [
            'can_manage_users' => 'boolean',
            'can_manage_roles' => 'boolean',
            'can_manage_permissions' => 'boolean',
            'can_invite_users' => 'boolean',
            'can_assign_managers' => 'boolean',
            'can_create_templates' => 'boolean',
            'can_assign_templates' => 'boolean',
            'can_view_own_permissions' => 'boolean',
            'additional_settings' => 'array',
        ];
    }

    public static function getForPanel(string $panelId): ?self
    {
        return static::where('panel_id', $panelId)->first();
    }

    public function canPerform(string $capability): bool
    {
        return $this->getAttribute($capability) ?? false;
    }
}
