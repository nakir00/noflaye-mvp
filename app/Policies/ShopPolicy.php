<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Shop;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class ShopPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::SHOP_VIEW_ANY);
    }

    public function view(User $user, Shop $shop): bool
    {
        // Check global permission
        if ($this->can($user, Permission::SHOP_VIEW)) {
            return true;
        }

        // Check scoped permission (user manages this shop)
        return $this->can($user, Permission::SHOP_VIEW, $shop->id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::SHOP_CREATE);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_UPDATE)
            || $this->can($user, Permission::SHOP_UPDATE, $shop->id);
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_DELETE);
    }

    public function manageStaff(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_MANAGE_STAFF, $shop->id);
    }
}
