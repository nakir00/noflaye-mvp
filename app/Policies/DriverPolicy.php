<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Driver;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

/**
 * Driver Policy
 *
 * Handles authorization logic for Driver model operations using the
 * type-safe permission system. Supports both global and scoped permissions.
 *
 * Features:
 * - Request-level permission caching via ChecksPermissions trait
 * - Global and scoped permission checks
 * - Standard CRUD operations (viewAny, view, create, update, delete)
 * - Driver assignment permissions
 *
 * Usage:
 * ```php
 * // In controller
 * $this->authorize('view', $driver);
 * $this->authorize('update', $driver);
 *
 * // In Blade/Components
 *
 * @can('create', App\Models\Driver::class)
 * @can('update', $driver)
 * ```
 *
 * @see ChecksPermissions For permission checking implementation
 * @see Permission For available driver permissions
 */
class DriverPolicy
{
    use ChecksPermissions;

    /**
     * Determine if user can view any drivers
     *
     * Checks global DRIVER_VIEW_ANY permission for listing drivers.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can view drivers list
     *
     * @example
     * // Required for index/list pages
     * Gate::authorize('viewAny', Driver::class);
     */
    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::DRIVER_VIEW_ANY);
    }

    /**
     * Determine if user can view a specific driver
     *
     * Checks both global and scoped DRIVER_VIEW permission.
     * Scoped permission allows viewing drivers assigned to user's entities.
     *
     * @param  User  $user  The authenticated user
     * @param  Driver  $driver  The driver to view
     * @return bool True if user can view this driver
     *
     * @example
     * // Global permission
     * if ($user->can('view', $driver)) { }
     *
     * // Scoped permission (driver assigned to user's shop/kitchen)
     * Gate::authorize('view', $driver);
     */
    public function view(User $user, Driver $driver): bool
    {
        // Check global permission
        if ($this->can($user, Permission::DRIVER_VIEW)) {
            return true;
        }

        // Check scoped permission (user manages this driver)
        return $this->can($user, Permission::DRIVER_VIEW, $driver->id);
    }

    /**
     * Determine if user can create drivers
     *
     * Checks global DRIVER_CREATE permission.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can create drivers
     *
     * @example
     * Gate::authorize('create', Driver::class);
     */
    public function create(User $user): bool
    {
        return $this->can($user, Permission::DRIVER_CREATE);
    }

    /**
     * Determine if user can update a driver
     *
     * Checks both global and scoped DRIVER_UPDATE permission.
     * Scoped permission allows updating drivers assigned to user's entities.
     *
     * @param  User  $user  The authenticated user
     * @param  Driver  $driver  The driver to update
     * @return bool True if user can update this driver
     *
     * @example
     * Gate::authorize('update', $driver);
     */
    public function update(User $user, Driver $driver): bool
    {
        return $this->can($user, Permission::DRIVER_UPDATE)
            || $this->can($user, Permission::DRIVER_UPDATE, $driver->id);
    }

    /**
     * Determine if user can delete a driver
     *
     * Checks global DRIVER_DELETE permission.
     * Only users with global delete permission can delete drivers.
     *
     * @param  User  $user  The authenticated user
     * @param  Driver  $driver  The driver to delete
     * @return bool True if user can delete this driver
     *
     * @example
     * Gate::authorize('delete', $driver);
     */
    public function delete(User $user, Driver $driver): bool
    {
        return $this->can($user, Permission::DRIVER_DELETE);
    }

    /**
     * Determine if user can assign a driver to entities
     *
     * Checks scoped DRIVER_ASSIGN permission.
     * Allows assigning drivers to shops/kitchens the user manages.
     *
     * @param  User  $user  The authenticated user
     * @param  Driver  $driver  The driver to assign
     * @param  int|null  $scopeId  Optional scope (shop/kitchen ID)
     * @return bool True if user can assign this driver
     *
     * @example
     * // Assign driver to a specific shop
     * Gate::authorize('assign', [$driver, $shopId]);
     */
    public function assign(User $user, Driver $driver, ?int $scopeId = null): bool
    {
        return $this->can($user, Permission::DRIVER_ASSIGN, $scopeId);
    }
}
