<?php

namespace App\Enums;

enum Permission: string
{
    // ========================================
    // USER PERMISSIONS
    // ========================================
    case USER_VIEW_ANY = 'users.viewAny';
    case USER_VIEW = 'users.view';
    case USER_CREATE = 'users.create';
    case USER_UPDATE = 'users.update';
    case USER_DELETE = 'users.delete';
    case USER_RESTORE = 'users.restore';
    case USER_FORCE_DELETE = 'users.forceDelete';
    case USER_ASSIGN_TEMPLATE = 'users.assignTemplate';
    case USER_REVOKE_TEMPLATE = 'users.revokeTemplate';
    case USER_ASSIGN_PERMISSION = 'users.assignPermission';
    case USER_REVOKE_PERMISSION = 'users.revokePermission';

    // ========================================
    // PERMISSION MANAGEMENT
    // ========================================
    case PERMISSION_VIEW_ANY = 'permissions.viewAny';
    case PERMISSION_VIEW = 'permissions.view';
    case PERMISSION_CREATE = 'permissions.create';
    case PERMISSION_UPDATE = 'permissions.update';
    case PERMISSION_DELETE = 'permissions.delete';

    // ========================================
    // TEMPLATE MANAGEMENT
    // ========================================
    case TEMPLATE_VIEW_ANY = 'templates.viewAny';
    case TEMPLATE_VIEW = 'templates.view';
    case TEMPLATE_CREATE = 'templates.create';
    case TEMPLATE_UPDATE = 'templates.update';
    case TEMPLATE_DELETE = 'templates.delete';
    case TEMPLATE_ASSIGN = 'templates.assign';

    // ========================================
    // SHOP PERMISSIONS
    // ========================================
    case SHOP_VIEW_ANY = 'shops.viewAny';
    case SHOP_VIEW = 'shops.view';
    case SHOP_CREATE = 'shops.create';
    case SHOP_UPDATE = 'shops.update';
    case SHOP_DELETE = 'shops.delete';
    case SHOP_MANAGE_STAFF = 'shops.manageStaff';

    // ========================================
    // KITCHEN PERMISSIONS
    // ========================================
    case KITCHEN_VIEW_ANY = 'kitchens.viewAny';
    case KITCHEN_VIEW = 'kitchens.view';
    case KITCHEN_CREATE = 'kitchens.create';
    case KITCHEN_UPDATE = 'kitchens.update';
    case KITCHEN_DELETE = 'kitchens.delete';
    case KITCHEN_MANAGE_STAFF = 'kitchens.manageStaff';

    // ========================================
    // DRIVER PERMISSIONS
    // ========================================
    case DRIVER_VIEW_ANY = 'drivers.viewAny';
    case DRIVER_VIEW = 'drivers.view';
    case DRIVER_CREATE = 'drivers.create';
    case DRIVER_UPDATE = 'drivers.update';
    case DRIVER_DELETE = 'drivers.delete';
    case DRIVER_ASSIGN = 'drivers.assign';

    // ========================================
    // SUPERVISOR PERMISSIONS
    // ========================================
    case SUPERVISOR_VIEW_ANY = 'supervisors.viewAny';
    case SUPERVISOR_VIEW = 'supervisors.view';
    case SUPERVISOR_CREATE = 'supervisors.create';
    case SUPERVISOR_UPDATE = 'supervisors.update';
    case SUPERVISOR_DELETE = 'supervisors.delete';
    case SUPERVISOR_ASSIGN = 'supervisors.assign';

    // ========================================
    // SUPPLIER PERMISSIONS
    // ========================================
    case SUPPLIER_VIEW_ANY = 'suppliers.viewAny';
    case SUPPLIER_VIEW = 'suppliers.view';
    case SUPPLIER_CREATE = 'suppliers.create';
    case SUPPLIER_UPDATE = 'suppliers.update';
    case SUPPLIER_DELETE = 'suppliers.delete';
    case SUPPLIER_MANAGE = 'suppliers.manage';

    // ========================================
    // DELEGATION PERMISSIONS
    // ========================================
    case DELEGATION_VIEW_ANY = 'delegations.viewAny';
    case DELEGATION_VIEW = 'delegations.view';
    case DELEGATION_CREATE = 'delegations.create';
    case DELEGATION_UPDATE = 'delegations.update';
    case DELEGATION_DELETE = 'delegations.delete';
    case DELEGATION_APPROVE = 'delegations.approve';
    case DELEGATION_REJECT = 'delegations.reject';

    // ========================================
    // REQUEST PERMISSIONS
    // ========================================
    case REQUEST_VIEW_ANY = 'requests.viewAny';
    case REQUEST_VIEW = 'requests.view';
    case REQUEST_CREATE = 'requests.create';
    case REQUEST_UPDATE = 'requests.update';
    case REQUEST_DELETE = 'requests.delete';
    case REQUEST_APPROVE = 'requests.approve';
    case REQUEST_REJECT = 'requests.reject';

    // ========================================
    // AUDIT PERMISSIONS
    // ========================================
    case AUDIT_VIEW_ANY = 'audit.viewAny';
    case AUDIT_VIEW = 'audit.view';
    case AUDIT_EXPORT = 'audit.export';

    // ========================================
    // WILDCARD PERMISSIONS
    // ========================================
    case WILDCARD_VIEW_ANY = 'wildcards.viewAny';
    case WILDCARD_VIEW = 'wildcards.view';
    case WILDCARD_CREATE = 'wildcards.create';
    case WILDCARD_UPDATE = 'wildcards.update';
    case WILDCARD_DELETE = 'wildcards.delete';

    // ========================================
    // SCOPE PERMISSIONS
    // ========================================
    case SCOPE_VIEW_ANY = 'scopes.viewAny';
    case SCOPE_VIEW = 'scopes.view';
    case SCOPE_CREATE = 'scopes.create';
    case SCOPE_UPDATE = 'scopes.update';
    case SCOPE_DELETE = 'scopes.delete';

    /**
     * Get all permissions for a specific resource
     *
     * Filters all permission cases to return only those belonging to a given resource.
     * This is useful for displaying resource-specific permissions in UI or for validation.
     *
     * @param  string  $resource  The resource name (e.g., 'users', 'shops', 'permissions')
     * @return array<self> Array of Permission enum cases for the given resource
     *
     * @example
     * $userPermissions = Permission::forResource('users');
     * // Returns [USER_VIEW_ANY, USER_VIEW, USER_CREATE, USER_UPDATE, ...]
     */
    public static function forResource(string $resource): array
    {
        return array_filter(
            self::cases(),
            fn (self $case) => str_starts_with($case->value, $resource.'.')
        );
    }

    /**
     * Try to create a Permission enum from a string value
     *
     * Attempts to match the given string to a Permission enum case.
     * Returns null if no match is found, allowing fallback to custom permissions.
     * This is the recommended way to handle both enum and dynamic permissions.
     *
     * @param  string  $value  The permission slug (e.g., 'users.view', 'custom.permission')
     * @return self|null Permission enum case if found, null otherwise
     *
     * @example
     * $permission = Permission::tryFromValue('users.view');
     * if ($permission) {
     *     // Handle enum permission
     * } else {
     *     // Handle custom/dynamic permission
     * }
     */
    public static function tryFromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Check if a permission value exists in the enum
     *
     * Determines whether a given string corresponds to a defined Permission case.
     * Useful for validation before attempting to use a permission.
     *
     * @param  string  $value  The permission slug to check
     * @return bool True if the permission exists in the enum, false otherwise
     *
     * @example
     * if (Permission::exists('users.view')) {
     *     // Safe to use as enum
     * }
     */
    public static function exists(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    /**
     * Get Permission enum from string or return the string itself
     *
     * This method provides a unified interface for handling both enum-based
     * and custom dynamic permissions. If the value matches an enum case,
     * it returns the enum; otherwise, it returns the string for custom permissions.
     *
     * @param  string  $value  The permission slug
     * @return self|string Permission enum if exists, otherwise the original string
     *
     * @example
     * $permission = Permission::fromString('users.view');
     * // Returns Permission::USER_VIEW enum
     *
     * $customPermission = Permission::fromString('custom.action');
     * // Returns 'custom.action' string for dynamic permission
     */
    public static function fromString(string $value): self|string
    {
        return self::tryFrom($value) ?? $value;
    }

    /**
     * Get a human-readable label for the permission
     *
     * Converts the permission enum case into a user-friendly string.
     * Used in admin panels, permission management UIs, and audit logs.
     *
     * @return string Human-readable permission label
     *
     * @example
     * Permission::USER_VIEW_ANY->label(); // Returns "View Any User"
     * Permission::SHOP_MANAGE_STAFF->label(); // Returns "Manage Shop Staff"
     */
    public function label(): string
    {
        // Convert from 'users.viewAny' to 'View Any Users'
        $parts = explode('.', $this->value);
        $resource = ucfirst($parts[0]);
        $action = $parts[1] ?? '';

        // Convert camelCase action to words
        $actionWords = preg_replace('/([a-z])([A-Z])/', '$1 $2', $action);
        $actionWords = ucwords($actionWords);

        return trim("{$actionWords} {$resource}");
    }

    /**
     * Check if permission is for a specific action
     *
     * Determines if this permission's action matches the given action name.
     *
     * @param  string  $action  The action to check (e.g., 'view', 'create', 'delete')
     * @return bool True if the permission is for the specified action
     *
     * @example
     * Permission::USER_VIEW->isAction('view'); // true
     * Permission::USER_CREATE->isAction('view'); // false
     */
    public function isAction(string $action): bool
    {
        return str_ends_with($this->value, '.'.$action);
    }

    /**
     * Check if this is a "viewAny" permission
     *
     * ViewAny permissions allow listing/viewing collections of resources.
     * These are typically used for index pages and list views.
     *
     * @return bool True if this is a viewAny permission
     *
     * @example
     * Permission::USER_VIEW_ANY->isViewAny(); // true
     * Permission::USER_VIEW->isViewAny(); // false
     */
    public function isViewAny(): bool
    {
        return $this->isAction('viewAny');
    }

    /**
     * Check if this is a destructive permission
     *
     * Destructive permissions can permanently alter or remove data.
     * Used for additional confirmation prompts and audit logging.
     *
     * @return bool True if the permission is destructive (delete or forceDelete)
     *
     * @example
     * Permission::USER_DELETE->isDestructive(); // true
     * Permission::USER_FORCE_DELETE->isDestructive(); // true
     * Permission::USER_UPDATE->isDestructive(); // false
     */
    public function isDestructive(): bool
    {
        return $this->isAction('delete') || $this->isAction('forceDelete');
    }

    /**
     * Get the resource name from the permission
     *
     * Extracts the resource portion of the permission slug.
     *
     * @return string The resource name (e.g., 'users', 'shops', 'permissions')
     *
     * @example
     * Permission::USER_VIEW->resource(); // 'users'
     * Permission::SHOP_CREATE->resource(); // 'shops'
     */
    public function resource(): string
    {
        return explode('.', $this->value)[0];
    }

    /**
     * Get the action name from the permission
     *
     * Extracts the action portion of the permission slug.
     *
     * @return string The action name (e.g., 'view', 'create', 'delete')
     *
     * @example
     * Permission::USER_VIEW->action(); // 'view'
     * Permission::SHOP_CREATE->action(); // 'create'
     */
    public function action(): string
    {
        $parts = explode('.', $this->value);

        return end($parts);
    }
}
