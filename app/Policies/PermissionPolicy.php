<?php

namespace App\Policies;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class PermissionPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, PermissionEnum::PERMISSION_VIEW_ANY);
    }

    public function view(User $user, Permission $permission): bool
    {
        return $this->can($user, PermissionEnum::PERMISSION_VIEW);
    }

    public function create(User $user): bool
    {
        return $this->can($user, PermissionEnum::PERMISSION_CREATE);
    }

    public function update(User $user, Permission $permission): bool
    {
        // Cannot update system permissions
        if ($permission->is_system) {
            return false;
        }

        return $this->can($user, PermissionEnum::PERMISSION_UPDATE);
    }

    public function delete(User $user, Permission $permission): bool
    {
        // Cannot delete system permissions
        if ($permission->is_system) {
            return false;
        }

        return $this->can($user, PermissionEnum::PERMISSION_DELETE);
    }
}
