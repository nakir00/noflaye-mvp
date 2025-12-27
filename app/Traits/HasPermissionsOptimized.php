<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Scope;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Collection;

/**
 * HasPermissionsOptimized Trait
 *
 * Provides optimized permission checking methods for User model
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
trait HasPermissionsOptimized
{
    /**
     * Check if user can perform action
     *
     * @param string $permission Permission slug
     * @param Scope|int|string|null $scope Scope (instance, id, or key)
     * @return bool
     */
    public function can($permission, $scope = null): bool
    {
        $checker = app(PermissionChecker::class);

        // Resolve scope
        $scopeInstance = $this->resolveScope($scope);

        return $checker->checkWithScope($this, $permission, $scopeInstance);
    }

    /**
     * Check if user has template assigned
     *
     * @param string $templateSlug Template slug
     * @return bool
     */
    public function hasTemplate(string $templateSlug): bool
    {
        return $this->templates()
            ->where('slug', $templateSlug)
            ->exists();
    }

    /**
     * Check if user has active delegation for permission
     *
     * @param string $permission Permission slug
     * @param Scope|null $scope Scope context
     * @return bool
     */
    public function hasDelegation(string $permission, ?Scope $scope = null): bool
    {
        $checker = app(PermissionChecker::class);

        return $checker->hasDelegatedPermission($this, $permission, $scope);
    }

    /**
     * Get all user permissions with scope
     *
     * @param Scope|int|string|null $scope Scope filter
     * @return Collection<Permission>
     */
    public function getAllPermissions($scope = null): Collection
    {
        $checker = app(PermissionChecker::class);

        $scopeInstance = $this->resolveScope($scope);

        return $checker->getAllUserPermissions($this, $scopeInstance);
    }

    /**
     * Check if user can perform any of the given permissions
     *
     * @param array $permissions Array of permission slugs
     * @param Scope|null $scope Scope context
     * @return bool
     */
    public function canAny(array $permissions, $scope = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission, $scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can perform all of the given permissions
     *
     * @param array $permissions Array of permission slugs
     * @param Scope|null $scope Scope context
     * @return bool
     */
    public function canAll(array $permissions, $scope = null): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission, $scope)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve scope from various inputs
     *
     * @param Scope|int|string|null $scope
     * @return Scope|null
     */
    private function resolveScope($scope): ?Scope
    {
        if ($scope instanceof Scope) {
            return $scope;
        }

        if (is_int($scope)) {
            return Scope::find($scope);
        }

        if (is_string($scope)) {
            return Scope::where('scope_key', $scope)->first();
        }

        return null;
    }
}
