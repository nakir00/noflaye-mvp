<?php

namespace App\Http\Middleware;

use App\Services\Permissions\PermissionChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: CheckPermission
 *
 * Route-level permission checking middleware for protecting endpoints.
 * This middleware integrates with the type-safe permission system to verify
 * that authenticated users have the required permissions before accessing routes.
 *
 * Features:
 * - Type-safe: Uses Permission enum for compile-time safety
 * - Scoped permissions: Supports checking permissions within specific scopes
 * - Flexible: Can check single permission, any of multiple, or all of multiple
 * - Customizable: Configurable error responses and redirects
 *
 * Usage in routes:
 * ```php
 * Route::get('/users', UserController::class)
 *     ->middleware('permission:users.viewAny');
 *
 * Route::get('/shops/{shop}', [ShopController::class, 'show'])
 *     ->middleware('permission:shops.view,scope:shop');
 *
 * Route::post('/admin/settings', SettingsController::class)
 *     ->middleware('permission:settings.update|admin.access');
 * ```
 *
 * Syntax:
 * - Single permission: 'permission:users.view'
 * - Multiple (any): 'permission:users.view|users.update'
 * - Multiple (all): 'permission:users.view&users.update'
 * - With scope: 'permission:shops.view,scope:shop' (uses route parameter)
 */
class CheckPermission
{
    /**
     * Handle an incoming request
     *
     * Checks if the authenticated user has the required permission(s).
     * Returns 403 Forbidden if permission is denied.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     *
     * @example
     * // In routes/web.php
     * Route::middleware(['auth', 'permission:users.viewAny'])->group(function () {
     *     Route::get('/users', [UserController::class, 'index']);
     * });
     */
    public function handle(Request $request, Closure $next, string $permissions, ?string $scope = null): Response
    {
        // Ensure user is authenticated
        if (! $request->user()) {
            abort(401, 'Unauthenticated');
        }

        $user = $request->user();
        $permissionChecker = app(PermissionChecker::class);

        // Parse scope from route parameters if specified
        $scopeId = $this->parseScopeId($request, $scope);

        // Check permissions based on operator
        if (str_contains($permissions, '&')) {
            // ALL permissions required (AND logic)
            $permissionList = explode('&', $permissions);
            $hasPermission = $this->checkAll($user->id, $permissionList, $scopeId, $permissionChecker);
        } elseif (str_contains($permissions, '|')) {
            // ANY permission required (OR logic)
            $permissionList = explode('|', $permissions);
            $hasPermission = $this->checkAny($user->id, $permissionList, $scopeId, $permissionChecker);
        } else {
            // Single permission required
            $hasPermission = $permissionChecker->userHasPermission(
                userId: $user->id,
                permission: $permissions,
                scopeId: $scopeId
            );
        }

        if (! $hasPermission) {
            abort(403, 'This action is unauthorized.');
        }

        return $next($request);
    }

    /**
     * Check if user has ALL permissions (AND logic)
     *
     * @param  int  $userId  The user's ID
     * @param  array<string>  $permissions  Array of permission slugs
     * @param  int|null  $scopeId  Optional scope ID
     * @param  PermissionChecker  $checker  The permission checker service
     * @return bool True if user has all permissions
     */
    protected function checkAll(int $userId, array $permissions, ?int $scopeId, PermissionChecker $checker): bool
    {
        foreach ($permissions as $permission) {
            if (! $checker->userHasPermission($userId, trim($permission), $scopeId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has ANY permission (OR logic)
     *
     * @param  int  $userId  The user's ID
     * @param  array<string>  $permissions  Array of permission slugs
     * @param  int|null  $scopeId  Optional scope ID
     * @param  PermissionChecker  $checker  The permission checker service
     * @return bool True if user has at least one permission
     */
    protected function checkAny(int $userId, array $permissions, ?int $scopeId, PermissionChecker $checker): bool
    {
        foreach ($permissions as $permission) {
            if ($checker->userHasPermission($userId, trim($permission), $scopeId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse scope ID from route parameters
     *
     * If scope is specified as 'scope:shop', this will extract the 'shop' parameter
     * from the route and use its ID as the scope ID.
     *
     * @param  Request  $request  The HTTP request
     * @param  string|null  $scope  The scope specification (e.g., 'scope:shop')
     * @return int|null The resolved scope ID or null
     *
     * @example
     * // Route: /shops/{shop}/edit
     * // Middleware: permission:shops.update,scope:shop
     * // Will use the {shop} route parameter's ID as scope
     */
    protected function parseScopeId(Request $request, ?string $scope): ?int
    {
        if (! $scope || ! str_starts_with($scope, 'scope:')) {
            return null;
        }

        $paramName = str_replace('scope:', '', $scope);
        $model = $request->route($paramName);

        // If route parameter is a model instance, get its ID
        if (is_object($model) && method_exists($model, 'getKey')) {
            return $model->getKey();
        }

        // If route parameter is numeric, use it directly
        if (is_numeric($model)) {
            return (int) $model;
        }

        return null;
    }
}
