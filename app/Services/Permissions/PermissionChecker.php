<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\Permission;
use App\Models\Scope;
use App\Models\PermissionDelegation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * PermissionChecker Service
 *
 * Check user permissions with scope and condition support
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionChecker
{
    public function __construct(
        private ConditionEvaluator $conditionEvaluator,
        private WildcardExpander $wildcardExpander,
        private ScopeManager $scopeManager
    ) {}

    /**
     * Check if user has permission with scope
     *
     * @param User $user
     * @param string $permissionSlug
     * @param Scope|int|null $scope
     * @param Request|null $request
     * @return bool
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

        // Check template permissions
        if ($this->hasTemplatePermission($user, $permissionSlug, $scopeInstance, $request)) {
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
     *
     * @param User $user
     * @param string $permissionSlug
     * @param array $conditions
     * @param Request|null $request
     * @return bool
     */
    public function checkWithConditions(
        User $user,
        string $permissionSlug,
        array $conditions,
        ?Request $request = null
    ): bool {
        // First check if user has permission at all
        if (!$this->checkWithScope($user, $permissionSlug, null, $request)) {
            return false;
        }

        // Then evaluate conditions
        return $this->conditionEvaluator->evaluate($conditions, $user, $request);
    }

    /**
     * Check if user has delegated permission
     *
     * @param User $user
     * @param string $permissionSlug
     * @param Scope|null $scope
     * @param Request|null $request
     * @return bool
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
            ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
            ->first();

        return $delegation !== null;
    }

    /**
     * Get all user permissions with scope
     *
     * @param User $user
     * @param Scope|null $scope
     * @return Collection<Permission>
     */
    public function getAllUserPermissions(User $user, ?Scope $scope = null): Collection
    {
        $cacheKey = "user:{$user->id}:permissions:" . ($scope?->id ?? 'global');

        return Cache::remember($cacheKey, 600, function () use ($user, $scope) {
            $permissions = collect();

            // Direct permissions
            $directPerms = $user->permissions()
                ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
                ->get();

            $permissions = $permissions->merge($directPerms);

            // Template permissions
            $templates = $user->templates()
                ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
                ->with(['permissions', 'wildcards'])
                ->get();

            foreach ($templates as $template) {
                // Direct template permissions
                $permissions = $permissions->merge($template->getAllPermissions());

                // Wildcard expanded permissions
                $wildcardPerms = $this->wildcardExpander->expandForTemplate($template);
                $permissions = $permissions->merge($wildcardPerms);
            }

            // Delegated permissions
            $delegations = PermissionDelegation::active()
                ->where('delegatee_id', $user->id)
                ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
                ->with('permission')
                ->get();

            foreach ($delegations as $delegation) {
                $permissions->push($delegation->permission);
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
            ->when($scope, fn($q) => $q->where('user_permissions.scope_id', $scope->id))
            ->first();

        if (!$userPerm) {
            return false;
        }

        // Evaluate conditions if present
        $conditions = $userPerm->pivot->conditions ?? [];

        if (!empty($conditions)) {
            return $this->conditionEvaluator->evaluate($conditions, $user, $request);
        }

        return true;
    }

    /**
     * Check template permission
     */
    private function hasTemplatePermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope,
        ?Request $request
    ): bool {
        $templates = $user->templates()
            ->when($scope, fn($q) => $q->where('user_templates.scope_id', $scope->id))
            ->with(['permissions', 'wildcards'])
            ->get();

        foreach ($templates as $template) {
            // Check direct permissions
            if ($template->permissions->contains('slug', $permissionSlug)) {
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
