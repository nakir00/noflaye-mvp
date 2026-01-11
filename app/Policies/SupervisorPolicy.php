<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Supervisor;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

/**
 * Supervisor Policy
 *
 * Handles authorization logic for Supervisor model operations using the
 * type-safe permission system. Supports both global and scoped permissions.
 *
 * Features:
 * - Request-level permission caching via ChecksPermissions trait
 * - Global and scoped permission checks
 * - Standard CRUD operations (viewAny, view, create, update, delete)
 * - Supervisor assignment permissions
 *
 * Usage:
 * ```php
 * // In controller
 * $this->authorize('view', $supervisor);
 * $this->authorize('update', $supervisor);
 *
 * // In Blade/Components
 *
 * @can('create', App\Models\Supervisor::class)
 * @can('update', $supervisor)
 * ```
 *
 * @see ChecksPermissions For permission checking implementation
 * @see Permission For available supervisor permissions
 */
class SupervisorPolicy
{
    use ChecksPermissions;

    /**
     * Determine if user can view any supervisors
     *
     * Checks global SUPERVISOR_VIEW_ANY permission for listing supervisors.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can view supervisors list
     *
     * @example
     * // Required for index/list pages
     * Gate::authorize('viewAny', Supervisor::class);
     */
    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::SUPERVISOR_VIEW_ANY);
    }

    /**
     * Determine if user can view a specific supervisor
     *
     * Checks both global and scoped SUPERVISOR_VIEW permission.
     * Scoped permission allows viewing supervisors assigned to user's entities.
     *
     * @param  User  $user  The authenticated user
     * @param  Supervisor  $supervisor  The supervisor to view
     * @return bool True if user can view this supervisor
     *
     * @example
     * // Global permission
     * if ($user->can('view', $supervisor)) { }
     *
     * // Scoped permission (supervisor assigned to user's shop/kitchen)
     * Gate::authorize('view', $supervisor);
     */
    public function view(User $user, Supervisor $supervisor): bool
    {
        // Check global permission
        if ($this->can($user, Permission::SUPERVISOR_VIEW)) {
            return true;
        }

        // Check scoped permission (user manages this supervisor)
        return $this->can($user, Permission::SUPERVISOR_VIEW, $supervisor->id);
    }

    /**
     * Determine if user can create supervisors
     *
     * Checks global SUPERVISOR_CREATE permission.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can create supervisors
     *
     * @example
     * Gate::authorize('create', Supervisor::class);
     */
    public function create(User $user): bool
    {
        return $this->can($user, Permission::SUPERVISOR_CREATE);
    }

    /**
     * Determine if user can update a supervisor
     *
     * Checks both global and scoped SUPERVISOR_UPDATE permission.
     * Scoped permission allows updating supervisors assigned to user's entities.
     *
     * @param  User  $user  The authenticated user
     * @param  Supervisor  $supervisor  The supervisor to update
     * @return bool True if user can update this supervisor
     *
     * @example
     * Gate::authorize('update', $supervisor);
     */
    public function update(User $user, Supervisor $supervisor): bool
    {
        return $this->can($user, Permission::SUPERVISOR_UPDATE)
            || $this->can($user, Permission::SUPERVISOR_UPDATE, $supervisor->id);
    }

    /**
     * Determine if user can delete a supervisor
     *
     * Checks global SUPERVISOR_DELETE permission.
     * Only users with global delete permission can delete supervisors.
     *
     * @param  User  $user  The authenticated user
     * @param  Supervisor  $supervisor  The supervisor to delete
     * @return bool True if user can delete this supervisor
     *
     * @example
     * Gate::authorize('delete', $supervisor);
     */
    public function delete(User $user, Supervisor $supervisor): bool
    {
        return $this->can($user, Permission::SUPERVISOR_DELETE);
    }

    /**
     * Determine if user can assign a supervisor to entities
     *
     * Checks scoped SUPERVISOR_ASSIGN permission.
     * Allows assigning supervisors to shops/kitchens/drivers the user manages.
     *
     * @param  User  $user  The authenticated user
     * @param  Supervisor  $supervisor  The supervisor to assign
     * @param  int|null  $scopeId  Optional scope (shop/kitchen/driver ID)
     * @return bool True if user can assign this supervisor
     *
     * @example
     * // Assign supervisor to a specific shop
     * Gate::authorize('assign', [$supervisor, $shopId]);
     */
    public function assign(User $user, Supervisor $supervisor, ?int $scopeId = null): bool
    {
        return $this->can($user, Permission::SUPERVISOR_ASSIGN, $scopeId);
    }
}
