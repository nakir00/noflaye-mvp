<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class UserPolicy
{
    use ChecksPermissions;

    /**
     * Determine if user can view any users
     */
    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::USER_VIEW_ANY);
    }

    /**
     * Determine if user can view the model
     */
    public function view(User $user, User $model): bool
    {
        // Can view self
        if ($user->id === $model->id) {
            return true;
        }

        return $this->can($user, Permission::USER_VIEW);
    }

    /**
     * Determine if user can create users
     */
    public function create(User $user): bool
    {
        return $this->can($user, Permission::USER_CREATE);
    }

    /**
     * Determine if user can update the model
     */
    public function update(User $user, User $model): bool
    {
        // Cannot update self via this permission
        if ($user->id === $model->id) {
            return false;
        }

        return $this->can($user, Permission::USER_UPDATE);
    }

    /**
     * Determine if user can delete the model
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        return $this->can($user, Permission::USER_DELETE);
    }

    /**
     * Determine if user can restore the model
     */
    public function restore(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_RESTORE);
    }

    /**
     * Determine if user can permanently delete
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_FORCE_DELETE);
    }

    /**
     * Determine if user can assign templates
     */
    public function assignTemplate(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_ASSIGN_TEMPLATE);
    }

    /**
     * Determine if user can assign permissions
     */
    public function assignPermission(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_ASSIGN_PERMISSION);
    }
}
