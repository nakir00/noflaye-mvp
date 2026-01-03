<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Kitchen;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class KitchenPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::KITCHEN_VIEW_ANY);
    }

    public function view(User $user, Kitchen $kitchen): bool
    {
        if ($this->can($user, Permission::KITCHEN_VIEW)) {
            return true;
        }

        return $this->can($user, Permission::KITCHEN_VIEW, $kitchen->id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::KITCHEN_CREATE);
    }

    public function update(User $user, Kitchen $kitchen): bool
    {
        return $this->can($user, Permission::KITCHEN_UPDATE)
            || $this->can($user, Permission::KITCHEN_UPDATE, $kitchen->id);
    }

    public function delete(User $user, Kitchen $kitchen): bool
    {
        return $this->can($user, Permission::KITCHEN_DELETE);
    }

    public function manageStaff(User $user, Kitchen $kitchen): bool
    {
        return $this->can($user, Permission::KITCHEN_MANAGE_STAFF, $kitchen->id);
    }
}
