<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $panel_id
 * @property bool $can_manage_users
 * @property bool $can_manage_roles
 * @property bool $can_manage_permissions
 * @property bool $can_invite_users
 * @property bool $can_assign_managers
 * @property bool $can_create_templates
 * @property bool $can_assign_templates
 * @property bool $can_view_own_permissions
 * @property array<array-key, mixed>|null $additional_settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereAdditionalSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanAssignManagers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanAssignTemplates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanCreateTemplates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanInviteUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanManagePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanManageRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanManageUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCanViewOwnPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration wherePanelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelConfiguration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
