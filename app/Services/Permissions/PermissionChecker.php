<?php

namespace App\Services\Permissions;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\PermissionDelegation;
use App\Models\Scope;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * PermissionChecker Service
 *
 * Check user permissions with scope and condition support.
 * Supports hierarchical permission templates and user groups with override system.
 *
 * Permission Sources (checked in order):
 * 1. Direct user permissions
 * 2. Template permissions (with hierarchy inheritance and override)
 * 3. User group permissions (with hierarchy inheritance and override)
 * 4. Delegated permissions
 *
 * @author Noflaye Box Team
 *
 * @version 2.0.0
 */
class PermissionChecker
{
    public function __construct(
        private ConditionEvaluator $conditionEvaluator,
        private WildcardExpander $wildcardExpander,
        private ScopeManager $scopeManager
    ) {}

    /**
     * Check if user has permission (accepts string or enum)
     */
    public function userHasPermission(
        int $userId,
        string|PermissionEnum $permission,
        ?int $scopeId = null,
        array $context = []
    ): bool {
        $permissionSlug = $permission instanceof PermissionEnum
            ? $permission->value
            : $permission;

        return Cache::tags(['permissions', "user.{$userId}"])
            ->remember(
                "permission.{$userId}.{$permissionSlug}.{$scopeId}",
                3600,
                fn () => $this->checkPermission($userId, $permissionSlug, $scopeId, $context)
            );
    }

    /**
     * Check permission (internal method)
     */
    private function checkPermission(
        int $userId,
        string $permissionSlug,
        ?int $scopeId,
        array $context
    ): bool {
        // NOTE: load more relationships if needed from context
        $user = User::find($userId);
        if (! $user) {
            return false;
        }

        $scope = $scopeId ? Scope::find($scopeId) : null;

        return $this->checkWithScope($user, $permissionSlug, $scope);
    }

    /**
     * Check if user has permission with scope
     */
    public function checkWithScope(
        User $user,
        string $permissionSlug,
        Scope|int|null $scope = null,
        ?Request $request = null
    ): bool {
        // Get scope instance
        $scopeInstance = $this->resolveScope($scope);

        // Check direct permissions
        if ($this->hasDirectPermission($user, $permissionSlug, $scopeInstance, $request)) {
            return true;
        }

        // Check template permissions (with hierarchy and override)
        if ($this->hasTemplatePermission($user, $permissionSlug, $scopeInstance, $request)) {
            return true;
        }

        // Check user group permissions (with hierarchy and override)
        if ($this->hasUserGroupPermission($user, $permissionSlug, $scopeInstance)) {
            return true;
        }

        // Check delegated permissions
        if ($this->hasDelegatedPermission($user, $permissionSlug, $scopeInstance, $request)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has permission with conditions
     */
    public function checkWithConditions(
        User $user,
        string $permissionSlug,
        array $conditions,
        ?Request $request = null
    ): bool {
        // First check if user has permission at all
        if (! $this->checkWithScope($user, $permissionSlug, null, $request)) {
            return false;
        }

        // Then evaluate conditions
        return $this->conditionEvaluator->evaluate($conditions, $user, $request);
    }

    /**
     * Check if user has delegated permission
     */
    public function hasDelegatedPermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope = null,
        ?Request $request = null
    ): bool {
        $delegation = PermissionDelegation::active()
            ->where('delegatee_id', $user->id)
            ->where('permission_slug', $permissionSlug)
            ->when($scope, fn ($q) => $q->where('scope_id', $scope->id))
            ->first();

        return $delegation !== null;
    }

    /**
     * Get all user permissions with scope
     *
     * Collects permissions from:
     * 1. Direct user permissions
     * 2. Template permissions (including inherited via hierarchy, respecting overrides)
     * 3. User group permissions (including inherited via hierarchy, respecting overrides)
     * 4. Wildcard expanded permissions
     * 5. Delegated permissions
     *
     * @return Collection<Permission>
     */
    public function getAllUserPermissions(User $user, ?Scope $scope = null): Collection
    {
        $cacheKey = "user:{$user->id}:permissions:".($scope?->id ?? 'global');

        return Cache::remember($cacheKey, 600, function () use ($user, $scope) {
            $permissions = collect();

            // 1. Direct permissions
            $directPerms = $user->permissions()
                ->when($scope, fn ($q) => $q->where('scope_id', $scope->id))
                ->get();

            $permissions = $permissions->merge($directPerms);

            // 2. Template permissions (includes inherited and respects overrides)
            $templates = $user->templates()
                ->when($scope, fn ($q) => $q->where('scope_id', $scope->id))
                ->with(['wildcards'])
                ->get();

            foreach ($templates as $template) {
                // Get all effective permissions (inherited + own - denied)
                $permissions = $permissions->merge($template->getAllPermissions());

                // 4. Wildcard expanded permissions
                $wildcardPerms = $this->wildcardExpander->expandForTemplate($template);
                $permissions = $permissions->merge($wildcardPerms);
            }

            // 3. User group permissions (includes inherited and respects overrides)
            $userGroups = $user->userGroups()
                ->when($scope, fn ($q) => $q->wherePivot('scope_id', $scope->id))
                ->get();

            foreach ($userGroups as $group) {
                // Get all effective permissions from group (inherited + template + own - denied)
                $permissions = $permissions->merge($group->getAllPermissions());
            }

            // 5. Delegated permissions
            $delegations = PermissionDelegation::active()
                ->where('delegatee_id', $user->id)
                ->when($scope, fn ($q) => $q->where('scope_id', $scope->id))
                ->with('permission')
                ->get();

            foreach ($delegations as $delegation) {
                if ($delegation->permission) {
                    $permissions->push($delegation->permission);
                }
            }

            return $permissions->unique('id');
        });
    }

    /**
     * Check direct permission
     */
    private function hasDirectPermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope,
        ?Request $request
    ): bool {
        $userPerm = $user->permissions()
            ->where('slug', $permissionSlug)
            ->when($scope, fn ($q) => $q->where('user_permissions.scope_id', $scope->id))
            ->first();

        if (! $userPerm) {
            return false;
        }

        // Evaluate conditions if present
        $conditions = $userPerm->pivot->conditions ?? [];

        if (! empty($conditions)) {
            return $this->conditionEvaluator->evaluate($conditions, $user, $request);
        }

        return true;
    }

    /**
     * Check template permission
     *
     * Uses getAllPermissions() which respects:
     * - Hierarchical inheritance (permissions from parent templates)
     * - Override system (denied permissions block inherited ones)
     */
    private function hasTemplatePermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope,
        ?Request $request
    ): bool {
        $templates = $user->templates()
            ->when($scope, fn ($q) => $q->where('user_templates.scope_id', $scope->id))
            ->with(['wildcards'])
            ->get();

        foreach ($templates as $template) {
            // Check all effective permissions (includes inherited, respects overrides)
            $allPermissions = $template->getAllPermissions();
            if ($allPermissions->contains('slug', $permissionSlug)) {
                return true;
            }

            // Check wildcard patterns
            foreach ($template->wildcards as $wildcard) {
                $permission = Permission::where('slug', $permissionSlug)->first();

                if ($permission && $this->wildcardExpander->matchesPattern($permission, $wildcard->pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check user group permission
     *
     * Uses getAllPermissions() which respects:
     * - Hierarchical inheritance (permissions from parent groups)
     * - Template inheritance (permissions from group's template)
     * - Override system (denied permissions block inherited ones)
     */
    private function hasUserGroupPermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope
    ): bool {
        $userGroups = $user->userGroups()
            ->when($scope, fn ($q) => $q->wherePivot('scope_id', $scope->id))
            ->get();

        foreach ($userGroups as $group) {
            // Check all effective permissions (includes inherited + template, respects overrides)
            $allPermissions = $group->getAllPermissions();
            if ($allPermissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve scope from various inputs
     */
    private function resolveScope(Scope|int|null $scope): ?Scope
    {
        if ($scope instanceof Scope) {
            return $scope;
        }

        if (is_int($scope)) {
            return $this->scopeManager->getScopeById($scope);
        }

        return null;
    }

    /**
     * Invalidate user permission cache
     */
    public function invalidateUserCache(User $user): void
    {
        Cache::forget("user:{$user->id}:permissions:global");

        // Also invalidate all scope-specific caches
        $scopes = Scope::all();
        foreach ($scopes as $scope) {
            Cache::forget("user:{$user->id}:permissions:{$scope->id}");
        }
    }
}
