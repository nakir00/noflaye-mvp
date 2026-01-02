<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ============================================
        // GATE CONFIGURATION - PERMISSION SYSTEM
        // ============================================

        /**
         * Super Admin Check
         *
         * Les utilisateurs avec le template 'admin' ont accès à tout
         */
        Gate::before(function (User $user, string $ability) {
            // Check if user has admin template
            if ($user->primaryTemplate && $user->primaryTemplate->slug === 'admin') {
                return true; // Admin has all permissions
            }

            // Otherwise, check specific permission
            return $this->checkUserPermission($user, $ability);
        });
    }

    /**
     * Check if user has a specific permission
     */
    protected function checkUserPermission(User $user, string $ability): ?bool
    {
        // Convert ability to permission slug
        // Example: 'view_user' -> 'users.read'
        // Example: 'create_shop' -> 'shops.create'
        $permissionSlug = $this->convertAbilityToPermissionSlug($ability);

        // Check if user has permission through primary template
        if ($user->primaryTemplate) {
            $hasPermission = $user->primaryTemplate->permissions()
                ->where('slug', $permissionSlug)
                ->exists();

            if ($hasPermission) {
                return true;
            }
        }

        // Check direct user permissions
        $hasDirectPermission = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $user->id)
            ->where('permissions.slug', $permissionSlug)
            ->exists();

        if ($hasDirectPermission) {
            return true;
        }

        // Check all user templates (not just primary)
        foreach ($user->templates as $template) {
            if ($template->permissions()->where('slug', $permissionSlug)->exists()) {
                return true;
            }
        }

        // No permission found
        return null; // Continue to other gates/policies
    }

    /**
     * Convert Filament ability to permission slug
     *
     * Filament abilities: view_any, view, create, update, delete, restore, force_delete
     * Permission slugs: resource.action (e.g., users.read, shops.create)
     */
    protected function convertAbilityToPermissionSlug(string $ability): string
    {
        // Extract model and action from ability
        // Example: view_any_user -> [view_any, user]
        // Example: create_shop -> [create, shop]

        $parts = explode('_', $ability);

        // Get the action (first part or first two parts)
        $action = $parts[0];
        if (isset($parts[1]) && in_array($parts[0] . '_' . $parts[1], ['view_any', 'force_delete'])) {
            $action = $parts[0] . '_' . $parts[1];
            array_shift($parts); // Remove first part
        }
        array_shift($parts); // Remove action part

        // Get the resource (remaining parts)
        $resource = implode('_', $parts);

        // Convert to plural
        $resource = Str::plural($resource);

        // Map Filament actions to our permission actions
        $actionMap = [
            'view_any' => 'list',
            'view' => 'read',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete',
            'restore' => 'update',
            'force_delete' => 'delete',
        ];

        $mappedAction = $actionMap[$action] ?? $action;

        return "{$resource}.{$mappedAction}";
    }
}
