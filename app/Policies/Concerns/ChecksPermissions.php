<?php

namespace App\Policies\Concerns;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use App\Services\Permissions\PermissionChecker;

/**
 * Trait: ChecksPermissions
 *
 * Provides permission checking functionality for Laravel policies with request-level caching.
 * This trait reduces duplicate permission checks within a single request, improving performance
 * significantly in policies that check multiple permissions.
 *
 * Features:
 * - Request-level caching: Caches permission checks for the duration of the request
 * - Batch preloading: Preload multiple permissions at once for optimal performance
 * - Integration with PermissionChecker: Uses the centralized permission checking service
 *
 * Usage:
 * ```php
 * class UserPolicy
 * {
 *     use ChecksPermissions;
 *
 *     public function viewAny(User $user): bool
 *     {
 *         return $this->can($user, Permission::USER_VIEW_ANY);
 *     }
 * }
 * ```
 */
trait ChecksPermissions
{
    /**
     * Request-level permission cache
     *
     * Stores permission check results for the duration of the current request.
     * Cache key format: "{user_id}:{permission_slug}:{scope_id}"
     *
     * @var array<string, bool>
     */
    protected array $cachedPermissions = [];

    /**
     * Check if user has permission with request-level caching
     *
     * This method checks permissions through the PermissionChecker service
     * and caches the result for the duration of the request to avoid
     * redundant database queries.
     *
     * @param  User  $user  The user to check permissions for
     * @param  PermissionEnum  $permission  The permission to check
     * @param  int|null  $scopeId  Optional scope ID for scoped permissions
     * @return bool True if user has the permission, false otherwise
     *
     * @example
     * if ($this->can($user, Permission::SHOP_VIEW, $shopId)) {
     *     // User can view this shop
     * }
     */
    protected function can(User $user, PermissionEnum $permission, ?int $scopeId = null): bool
    {
        $cacheKey = $this->getCacheKey($user->id, $permission->value, $scopeId);

        // Return cached result if available
        if (array_key_exists($cacheKey, $this->cachedPermissions)) {
            return $this->cachedPermissions[$cacheKey];
        }

        // Check permission and cache result
        $result = app(PermissionChecker::class)->userHasPermission(
            userId: $user->id,
            permission: $permission->value,
            scopeId: $scopeId
        );

        $this->cachedPermissions[$cacheKey] = $result;

        return $result;
    }

    /**
     * Check if user has any of the given permissions
     *
     * Returns true as soon as one permission is found, short-circuiting
     * to avoid unnecessary permission checks.
     *
     * @param  User  $user  The user to check permissions for
     * @param  array<PermissionEnum>  $permissions  Array of permissions to check
     * @param  int|null  $scopeId  Optional scope ID for scoped permissions
     * @return bool True if user has at least one of the permissions
     *
     * @example
     * if ($this->canAny($user, [Permission::SHOP_VIEW, Permission::SHOP_UPDATE], $shopId)) {
     *     // User can either view or update this shop
     * }
     */
    protected function canAny(User $user, array $permissions, ?int $scopeId = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($user, $permission, $scopeId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     *
     * Returns false as soon as one permission is missing, short-circuiting
     * to avoid unnecessary permission checks.
     *
     * @param  User  $user  The user to check permissions for
     * @param  array<PermissionEnum>  $permissions  Array of permissions to check
     * @param  int|null  $scopeId  Optional scope ID for scoped permissions
     * @return bool True if user has all of the permissions
     *
     * @example
     * if ($this->canAll($user, [Permission::SHOP_VIEW, Permission::SHOP_UPDATE], $shopId)) {
     *     // User can both view and update this shop
     * }
     */
    protected function canAll(User $user, array $permissions, ?int $scopeId = null): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->can($user, $permission, $scopeId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Preload permissions for batch operations
     *
     * This method preloads multiple permissions at once, which is useful
     * when you know you'll be checking multiple permissions in a policy.
     * This reduces the number of database queries.
     *
     * @param  User  $user  The user to preload permissions for
     * @param  array<PermissionEnum>  $permissions  Array of permissions to preload
     * @param  int|null  $scopeId  Optional scope ID for scoped permissions
     *
     * @example
     * // In a policy method that checks multiple permissions
     * public function manageShop(User $user, Shop $shop): bool
     * {
     *     // Preload all permissions we'll check
     *     $this->preloadPermissions($user, [
     *         Permission::SHOP_VIEW,
     *         Permission::SHOP_UPDATE,
     *         Permission::SHOP_MANAGE_STAFF,
     *     ], $shop->id);
     *
     *     // Now these checks use the preloaded cache
     *     return $this->canAll($user, [...], $shop->id);
     * }
     */
    protected function preloadPermissions(User $user, array $permissions, ?int $scopeId = null): void
    {
        foreach ($permissions as $permission) {
            // This will populate the cache for each permission
            $this->can($user, $permission, $scopeId);
        }
    }

    /**
     * Generate cache key for permission check
     *
     * Creates a unique cache key combining user ID, permission slug, and scope ID.
     *
     * @param  int  $userId  The user's ID
     * @param  string  $permissionSlug  The permission slug
     * @param  int|null  $scopeId  Optional scope ID
     * @return string The cache key
     */
    protected function getCacheKey(int $userId, string $permissionSlug, ?int $scopeId): string
    {
        return "{$userId}:{$permissionSlug}:".($scopeId ?? 'null');
    }

    /**
     * Clear the request-level permission cache
     *
     * Useful in tests or when permissions are modified during the request.
     * Generally not needed in normal application flow.
     */
    protected function clearPermissionCache(): void
    {
        $this->cachedPermissions = [];
    }
}
