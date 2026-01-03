<?php

namespace App\Enums;

enum EntityType: string
{
    case USER = 'user';
    case SHOP = 'shop';
    case KITCHEN = 'kitchen';
    case DRIVER = 'driver';
    case SUPERVISOR = 'supervisor';
    case SUPPLIER = 'supplier';
    case PERMISSION = 'permission';
    case TEMPLATE = 'template';
    case DELEGATION = 'delegation';
    case REQUEST = 'request';

    /**
     * Get the Eloquent model class for this entity type
     *
     * Maps each entity type to its corresponding Eloquent model class.
     * Used for dynamic model resolution in authorization and data access layers.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model> Fully qualified model class name
     *
     * @example
     * EntityType::USER->modelClass(); // Returns \App\Models\User::class
     * EntityType::SHOP->modelClass(); // Returns \App\Models\Shop::class
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::USER => \App\Models\User::class,
            self::SHOP => \App\Models\Shop::class,
            self::KITCHEN => \App\Models\Kitchen::class,
            self::DRIVER => \App\Models\Driver::class,
            self::SUPERVISOR => \App\Models\Supervisor::class,
            self::SUPPLIER => \App\Models\Supplier::class,
            self::PERMISSION => \App\Models\Permission::class,
            self::TEMPLATE => \App\Models\PermissionTemplate::class,
            self::DELEGATION => \App\Models\PermissionDelegation::class,
            self::REQUEST => \App\Models\PermissionRequest::class,
        };
    }

    /**
     * Get human-readable label for the entity type
     *
     * Returns a localized French label for display in UIs.
     * Used in admin panels, dropdowns, and data tables.
     *
     * @return string Localized entity type name (singular)
     *
     * @example
     * EntityType::USER->label(); // Returns "Utilisateur"
     * EntityType::SHOP->label(); // Returns "Boutique"
     */
    public function label(): string
    {
        return match ($this) {
            self::USER => 'Utilisateur',
            self::SHOP => 'Boutique',
            self::KITCHEN => 'Cuisine',
            self::DRIVER => 'Chauffeur',
            self::SUPERVISOR => 'Superviseur',
            self::SUPPLIER => 'Fournisseur',
            self::PERMISSION => 'Permission',
            self::TEMPLATE => 'Template',
            self::DELEGATION => 'Délégation',
            self::REQUEST => 'Demande',
        };
    }

    /**
     * Get the plural form of the entity type
     *
     * Returns the plural form used in URLs, table names, and resource routing.
     * Matches Laravel's convention for plural resource names.
     *
     * @return string Plural form of the entity type
     *
     * @example
     * EntityType::USER->plural(); // Returns "users"
     * EntityType::SHOP->plural(); // Returns "shops"
     * EntityType::KITCHEN->plural(); // Returns "kitchens"
     */
    public function plural(): string
    {
        return match ($this) {
            self::USER => 'users',
            self::SHOP => 'shops',
            self::KITCHEN => 'kitchens',
            self::DRIVER => 'drivers',
            self::SUPERVISOR => 'supervisors',
            self::SUPPLIER => 'suppliers',
            self::PERMISSION => 'permissions',
            self::TEMPLATE => 'templates',
            self::DELEGATION => 'delegations',
            self::REQUEST => 'requests',
        };
    }
}
