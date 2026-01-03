<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\PermissionTemplate;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class TemplatePolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::TEMPLATE_VIEW_ANY);
    }

    public function view(User $user, PermissionTemplate $template): bool
    {
        return $this->can($user, Permission::TEMPLATE_VIEW);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::TEMPLATE_CREATE);
    }

    public function update(User $user, PermissionTemplate $template): bool
    {
        // Cannot update system templates
        if ($template->is_system) {
            return false;
        }

        return $this->can($user, Permission::TEMPLATE_UPDATE);
    }

    public function delete(User $user, PermissionTemplate $template): bool
    {
        // Cannot delete system templates
        if ($template->is_system) {
            return false;
        }

        return $this->can($user, Permission::TEMPLATE_DELETE);
    }

    public function assign(User $user): bool
    {
        return $this->can($user, Permission::TEMPLATE_ASSIGN);
    }
}
