<?php

namespace App\Enums;

enum Template: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case SHOP_MANAGER = 'shop_manager';
    case SHOP_STAFF = 'shop_staff';
    case KITCHEN_MANAGER = 'kitchen_manager';
    case KITCHEN_STAFF = 'kitchen_staff';
    case DRIVER = 'driver';
    case SUPERVISOR = 'supervisor';
    case SUPPLIER_MANAGER = 'supplier_manager';
    case CUSTOMER = 'customer';

    /**
     * Get human-readable label for the template
     *
     * Returns a localized French label for display in UIs.
     * Used in admin panels, user management interfaces, and registration forms.
     *
     * @return string Localized template name
     *
     * @example
     * Template::SHOP_MANAGER->label(); // Returns "Gérant de Boutique"
     * Template::ADMIN->label(); // Returns "Administrateur"
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::SHOP_MANAGER => 'Gérant de Boutique',
            self::SHOP_STAFF => 'Staff Boutique',
            self::KITCHEN_MANAGER => 'Chef de Cuisine',
            self::KITCHEN_STAFF => 'Staff Cuisine',
            self::DRIVER => 'Chauffeur',
            self::SUPERVISOR => 'Superviseur',
            self::SUPPLIER_MANAGER => 'Gestionnaire Fournisseur',
            self::CUSTOMER => 'Client',
        };
    }

    /**
     * Get the Filament panel identifier for this template
     *
     * Determines which Filament admin panel a user with this template should access.
     * Used for routing users to the appropriate admin interface after login.
     *
     * @return string The Filament panel ID ('admin', 'shop', 'kitchen', etc.)
     *
     * @example
     * Template::ADMIN->panel(); // Returns 'admin'
     * Template::SHOP_MANAGER->panel(); // Returns 'shop'
     * Template::CUSTOMER->panel(); // Returns 'customer'
     */
    public function panel(): string
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN => 'admin',
            self::SHOP_MANAGER, self::SHOP_STAFF => 'shop',
            self::KITCHEN_MANAGER, self::KITCHEN_STAFF => 'kitchen',
            self::DRIVER => 'driver',
            self::SUPERVISOR => 'supervisor',
            self::SUPPLIER_MANAGER => 'supplier',
            self::CUSTOMER => 'customer',
        };
    }

    /**
     * Get default permissions for template
     *
     * Returns an array of Permission enum cases that should be automatically
     * assigned when this template is applied to a user. This defines the
     * baseline permissions for each role in the system.
     *
     * @return array<Permission> Array of Permission enum cases
     *
     * @example
     * $permissions = Template::SHOP_MANAGER->defaultPermissions();
     * // Returns [Permission::SHOP_VIEW, Permission::SHOP_UPDATE, ...]
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => Permission::cases(),
            self::ADMIN => [
                Permission::USER_VIEW_ANY,
                Permission::USER_VIEW,
                Permission::USER_CREATE,
                Permission::USER_UPDATE,
                Permission::SHOP_VIEW_ANY,
                Permission::SHOP_VIEW,
                Permission::SHOP_CREATE,
                Permission::SHOP_UPDATE,
                Permission::KITCHEN_VIEW_ANY,
                Permission::KITCHEN_VIEW,
                Permission::KITCHEN_CREATE,
                Permission::KITCHEN_UPDATE,
                Permission::DRIVER_VIEW_ANY,
                Permission::DRIVER_VIEW,
                Permission::SUPERVISOR_VIEW_ANY,
                Permission::SUPERVISOR_VIEW,
                Permission::SUPPLIER_VIEW_ANY,
                Permission::SUPPLIER_VIEW,
                Permission::TEMPLATE_VIEW_ANY,
                Permission::TEMPLATE_VIEW,
                Permission::PERMISSION_VIEW_ANY,
                Permission::PERMISSION_VIEW,
                Permission::AUDIT_VIEW_ANY,
                Permission::AUDIT_VIEW,
            ],
            self::SHOP_MANAGER => [
                Permission::SHOP_VIEW,
                Permission::SHOP_UPDATE,
                Permission::SHOP_MANAGE_STAFF,
            ],
            self::SHOP_STAFF => [
                Permission::SHOP_VIEW,
            ],
            self::KITCHEN_MANAGER => [
                Permission::KITCHEN_VIEW,
                Permission::KITCHEN_UPDATE,
                Permission::KITCHEN_MANAGE_STAFF,
            ],
            self::KITCHEN_STAFF => [
                Permission::KITCHEN_VIEW,
            ],
            self::DRIVER => [
                Permission::DRIVER_VIEW,
            ],
            self::SUPERVISOR => [
                Permission::SUPERVISOR_VIEW,
                Permission::SHOP_VIEW_ANY,
                Permission::SHOP_VIEW,
                Permission::KITCHEN_VIEW_ANY,
                Permission::KITCHEN_VIEW,
                Permission::DRIVER_VIEW_ANY,
                Permission::DRIVER_VIEW,
            ],
            self::SUPPLIER_MANAGER => [
                Permission::SUPPLIER_VIEW,
                Permission::SUPPLIER_UPDATE,
                Permission::SUPPLIER_MANAGE,
            ],
            self::CUSTOMER => [],
        };
    }

    /**
     * Check if template has admin-level privileges
     *
     * Determines if this template grants administrative access to the system.
     * Admin templates typically have elevated permissions and access to sensitive areas.
     *
     * @return bool True if template is SUPER_ADMIN or ADMIN
     *
     * @example
     * Template::ADMIN->isAdmin(); // true
     * Template::SHOP_MANAGER->isAdmin(); // false
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    /**
     * Check if template can manage a specific entity type
     *
     * Determines if users with this template have management permissions
     * over a given entity type. Used for authorization in entity-specific contexts.
     *
     * @param  string  $entity  The entity type to check (e.g., 'shop', 'kitchen', 'supplier')
     * @return bool True if the template can manage the entity type
     *
     * @example
     * Template::SHOP_MANAGER->canManage('shop'); // true
     * Template::SHOP_MANAGER->canManage('kitchen'); // false
     * Template::SUPERVISOR->canManage('driver'); // true
     */
    public function canManage(string $entity): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN => true,
            self::SHOP_MANAGER => $entity === 'shop',
            self::KITCHEN_MANAGER => $entity === 'kitchen',
            self::SUPERVISOR => in_array($entity, ['shop', 'kitchen', 'driver']),
            self::SUPPLIER_MANAGER => $entity === 'supplier',
            default => false,
        };
    }
}
