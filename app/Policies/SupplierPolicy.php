<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

/**
 * Supplier Policy
 *
 * Handles authorization logic for Supplier model operations using the
 * type-safe permission system. Supports both global and scoped permissions.
 *
 * Features:
 * - Request-level permission caching via ChecksPermissions trait
 * - Global and scoped permission checks
 * - Standard CRUD operations (viewAny, view, create, update, delete)
 * - Supplier management permissions
 *
 * Usage:
 * ```php
 * // In controller
 * $this->authorize('view', $supplier);
 * $this->authorize('update', $supplier);
 *
 * // In Blade/Components
 *
 * @can('create', App\Models\Supplier::class)
 * @can('update', $supplier)
 * ```
 *
 * @see ChecksPermissions For permission checking implementation
 * @see Permission For available supplier permissions
 */
class SupplierPolicy
{
    use ChecksPermissions;

    /**
     * Determine if user can view any suppliers
     *
     * Checks global SUPPLIER_VIEW_ANY permission for listing suppliers.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can view suppliers list
     *
     * @example
     * // Required for index/list pages
     * Gate::authorize('viewAny', Supplier::class);
     */
    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::SUPPLIER_VIEW_ANY);
    }

    /**
     * Determine if user can view a specific supplier
     *
     * Checks both global and scoped SUPPLIER_VIEW permission.
     * Scoped permission allows viewing suppliers assigned to user's entities.
     *
     * @param  User  $user  The authenticated user
     * @param  Supplier  $supplier  The supplier to view
     * @return bool True if user can view this supplier
     *
     * @example
     * // Global permission
     * if ($user->can('view', $supplier)) { }
     *
     * // Scoped permission (supplier assigned to user's entity)
     * Gate::authorize('view', $supplier);
     */
    public function view(User $user, Supplier $supplier): bool
    {
        // Check global permission
        if ($this->can($user, Permission::SUPPLIER_VIEW)) {
            return true;
        }

        // Check scoped permission (user manages this supplier)
        return $this->can($user, Permission::SUPPLIER_VIEW, $supplier->id);
    }

    /**
     * Determine if user can create suppliers
     *
     * Checks global SUPPLIER_CREATE permission.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can create suppliers
     *
     * @example
     * Gate::authorize('create', Supplier::class);
     */
    public function create(User $user): bool
    {
        return $this->can($user, Permission::SUPPLIER_CREATE);
    }

    /**
     * Determine if user can update a supplier
     *
     * Checks both global and scoped SUPPLIER_UPDATE permission.
     * Scoped permission allows updating suppliers assigned to user's entities.
     *
     * @param  User  $user  The authenticated user
     * @param  Supplier  $supplier  The supplier to update
     * @return bool True if user can update this supplier
     *
     * @example
     * Gate::authorize('update', $supplier);
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $this->can($user, Permission::SUPPLIER_UPDATE)
            || $this->can($user, Permission::SUPPLIER_UPDATE, $supplier->id);
    }

    /**
     * Determine if user can delete a supplier
     *
     * Checks global SUPPLIER_DELETE permission.
     * Only users with global delete permission can delete suppliers.
     *
     * @param  User  $user  The authenticated user
     * @param  Supplier  $supplier  The supplier to delete
     * @return bool True if user can delete this supplier
     *
     * @example
     * Gate::authorize('delete', $supplier);
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->can($user, Permission::SUPPLIER_DELETE);
    }

    /**
     * Determine if user can manage a supplier
     *
     * Checks scoped SUPPLIER_MANAGE permission.
     * Allows full management of suppliers including staff, products, orders, etc.
     *
     * @param  User  $user  The authenticated user
     * @param  Supplier  $supplier  The supplier to manage
     * @return bool True if user can manage this supplier
     *
     * @example
     * // Check if user can fully manage supplier
     * if ($user->can('manage', $supplier)) {
     *     // Allow access to supplier dashboard, staff management, etc.
     * }
     */
    public function manage(User $user, Supplier $supplier): bool
    {
        return $this->can($user, Permission::SUPPLIER_MANAGE, $supplier->id);
    }
}
