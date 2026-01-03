<?php

namespace App\Enums;

/**
 * Enum: WildcardPattern
 *
 * Purpose: Predefined wildcard patterns for automatic permission expansion
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
enum WildcardPattern: string
{
    // Global wildcards
    case ALL = '*.*';
    case ALL_READ = '*.read';
    case ALL_WRITE = '*.write';
    case ALL_ADMIN = '*.admin';

    // Shops wildcards
    case SHOPS_ALL = 'shops.*';
    case SHOPS_READ = 'shops.read';
    case SHOPS_WRITE = 'shops.write';
    case SHOPS_ADMIN = 'shops.admin';

    // Users wildcards
    case USERS_ALL = 'users.*';
    case USERS_READ = 'users.read';
    case USERS_WRITE = 'users.write';
    case USERS_ADMIN = 'users.admin';

    // Products wildcards
    case PRODUCTS_ALL = 'products.*';
    case PRODUCTS_READ = 'products.read';
    case PRODUCTS_WRITE = 'products.write';

    // Orders wildcards
    case ORDERS_ALL = 'orders.*';
    case ORDERS_READ = 'orders.read';
    case ORDERS_WRITE = 'orders.write';

    // Settings wildcards
    case SETTINGS_ALL = 'settings.*';
    case SETTINGS_READ = 'settings.read';
    case SETTINGS_WRITE = 'settings.write';

    /**
     * Get all global patterns (*.*, *.read, etc.)
     *
     * @return array<WildcardPattern>
     */
    public static function globalPatterns(): array
    {
        return [
            self::ALL,
            self::ALL_READ,
            self::ALL_WRITE,
            self::ALL_ADMIN,
        ];
    }

    /**
     * Get all shop patterns
     *
     * @return array<WildcardPattern>
     */
    public static function shopPatterns(): array
    {
        return [
            self::SHOPS_ALL,
            self::SHOPS_READ,
            self::SHOPS_WRITE,
            self::SHOPS_ADMIN,
        ];
    }

    /**
     * Get all user patterns
     *
     * @return array<WildcardPattern>
     */
    public static function userPatterns(): array
    {
        return [
            self::USERS_ALL,
            self::USERS_READ,
            self::USERS_WRITE,
            self::USERS_ADMIN,
        ];
    }

    /**
     * Get patterns by resource type
     *
     * @param  string  $resource  Resource name (shops, users, products, etc.)
     * @return array<WildcardPattern>
     */
    public static function forResource(string $resource): array
    {
        return match ($resource) {
            'shops' => self::shopPatterns(),
            'users' => self::userPatterns(),
            'products' => [self::PRODUCTS_ALL, self::PRODUCTS_READ, self::PRODUCTS_WRITE],
            'orders' => [self::ORDERS_ALL, self::ORDERS_READ, self::ORDERS_WRITE],
            'settings' => [self::SETTINGS_ALL, self::SETTINGS_READ, self::SETTINGS_WRITE],
            default => [],
        };
    }

    /**
     * Get pattern description
     */
    public function description(): string
    {
        return match ($this) {
            self::ALL => 'All permissions on all resources',
            self::ALL_READ => 'Read permissions on all resources',
            self::ALL_WRITE => 'Write permissions (create, update, delete) on all resources',
            self::ALL_ADMIN => 'Full admin permissions on all resources',

            self::SHOPS_ALL => 'All permissions on shops',
            self::SHOPS_READ => 'Read shop details',
            self::SHOPS_WRITE => 'Create, update, delete shops',
            self::SHOPS_ADMIN => 'Full admin access to shops',

            self::USERS_ALL => 'All permissions on users',
            self::USERS_READ => 'Read user details',
            self::USERS_WRITE => 'Create, update, delete users',
            self::USERS_ADMIN => 'Full admin access to users',

            self::PRODUCTS_ALL => 'All permissions on products',
            self::PRODUCTS_READ => 'Read product details',
            self::PRODUCTS_WRITE => 'Create, update, delete products',

            self::ORDERS_ALL => 'All permissions on orders',
            self::ORDERS_READ => 'Read order details',
            self::ORDERS_WRITE => 'Create, update, delete orders',

            self::SETTINGS_ALL => 'All permissions on settings',
            self::SETTINGS_READ => 'Read settings',
            self::SETTINGS_WRITE => 'Update settings',
        };
    }

    /**
     * Get Heroicon for pattern
     */
    public function icon(): string
    {
        return match ($this) {
            self::ALL, self::ALL_READ, self::ALL_WRITE, self::ALL_ADMIN => 'heroicon-o-globe-alt',

            self::SHOPS_ALL, self::SHOPS_READ, self::SHOPS_WRITE, self::SHOPS_ADMIN => 'heroicon-o-building-storefront',

            self::USERS_ALL, self::USERS_READ, self::USERS_WRITE, self::USERS_ADMIN => 'heroicon-o-users',

            self::PRODUCTS_ALL, self::PRODUCTS_READ, self::PRODUCTS_WRITE => 'heroicon-o-cube',

            self::ORDERS_ALL, self::ORDERS_READ, self::ORDERS_WRITE => 'heroicon-o-shopping-cart',

            self::SETTINGS_ALL, self::SETTINGS_READ, self::SETTINGS_WRITE => 'heroicon-o-cog-6-tooth',
        };
    }

    /**
     * Get color for Filament badge
     */
    public function color(): string
    {
        return match ($this) {
            self::ALL, self::ALL_ADMIN => 'danger',
            self::ALL_READ => 'info',
            self::ALL_WRITE => 'warning',

            self::SHOPS_ADMIN, self::USERS_ADMIN => 'primary',
            self::SHOPS_WRITE, self::USERS_WRITE, self::PRODUCTS_WRITE, self::ORDERS_WRITE, self::SETTINGS_WRITE => 'warning',
            self::SHOPS_READ, self::USERS_READ, self::PRODUCTS_READ, self::ORDERS_READ, self::SETTINGS_READ => 'info',

            default => 'gray',
        };
    }

    /**
     * Get pattern type (full, resource, action, macro)
     */
    public function patternType(): string
    {
        if ($this->value === '*.*') {
            return 'full';
        }

        if (str_ends_with($this->value, '.*')) {
            return 'resource';
        }

        if (str_starts_with($this->value, '*.')) {
            return 'action';
        }

        return 'macro';
    }
}
