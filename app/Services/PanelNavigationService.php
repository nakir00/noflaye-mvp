<?php

namespace App\Services;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;

class PanelNavigationService
{
    /**
     * Get menu items for the Shop panel
     *
     * @return array<Action>
     */
    public static function getMenuItemsForShop(): array
    {
        return self::buildFlatMenuItems('shop');
    }

    /**
     * Get menu items for the Kitchen panel
     *
     * @return array<Action>
     */
    public static function getMenuItemsForKitchen(): array
    {
        return self::buildFlatMenuItems('kitchen');
    }

    /**
     * Get menu items for the Driver panel
     *
     * @return array<Action>
     */
    public static function getMenuItemsForDriver(): array
    {
        return self::buildFlatMenuItems('driver');
    }

    /**
     * Get menu items for the Supervisor panel
     *
     * @return array<Action>
     */
    public static function getMenuItemsForSupervisor(): array
    {
        return self::buildFlatMenuItems('supervisor');
    }

    /**
     * Get menu items for the Supplier panel
     *
     * @return array<Action>
     */
    public static function getMenuItemsForSupplier(): array
    {
        return self::buildFlatMenuItems('supplier');
    }

    /**
     * Build a flat list of menu items for all accessible tenants
     *
     * @return array<Action>
     */
    private static function buildFlatMenuItems(string $currentPanelId): array
    {
        $menuItems = [];

        // Admin panel link
        $menuItems['admin'] = Action::make('nav_admin')
            ->label('Administration')
            ->icon('heroicon-o-shield-check')
            ->url('/admin')
            ->visible(fn (): bool => self::getUser()?->hasAnyTemplate(['super_admin', 'admin']) ?? false);

        // Build items dynamically
        for ($i = 0; $i < 10; $i++) {
            $idx = $i;

            // Shops
            $menuItems["shop_{$i}"] = Action::make("nav_shop_{$i}")
                ->label(fn (): string => self::getTenantLabel('shops', $idx, 'boutique'))
                ->icon('heroicon-o-building-storefront')
                ->url(fn (): string => self::getTenantUrl('shops', $idx, 'shop'))
                ->visible(fn (): bool => self::isTenantVisible('shops', $idx, 'shop', $currentPanelId));

            // Kitchens
            $menuItems["kitchen_{$i}"] = Action::make("nav_kitchen_{$i}")
                ->label(fn (): string => self::getTenantLabel('kitchens', $idx, 'cuisine'))
                ->icon('heroicon-o-fire')
                ->url(fn (): string => self::getTenantUrl('kitchens', $idx, 'kitchen'))
                ->visible(fn (): bool => self::isTenantVisible('kitchens', $idx, 'kitchen', $currentPanelId));

            // Drivers
            $menuItems["driver_{$i}"] = Action::make("nav_driver_{$i}")
                ->label(fn (): string => self::getTenantLabel('drivers', $idx, 'driver'))
                ->icon('heroicon-o-truck')
                ->url(fn (): string => self::getTenantUrl('drivers', $idx, 'driver'))
                ->visible(fn (): bool => self::isTenantVisible('drivers', $idx, 'driver', $currentPanelId));

            // Supervisors
            $menuItems["supervisor_{$i}"] = Action::make("nav_supervisor_{$i}")
                ->label(fn (): string => self::getTenantLabel('supervisors', $idx, 'superviseur'))
                ->icon('heroicon-o-eye')
                ->url(fn (): string => self::getTenantUrl('supervisors', $idx, 'supervisor'))
                ->visible(fn (): bool => self::isTenantVisible('supervisors', $idx, 'supervisor', $currentPanelId));

            // Suppliers
            $menuItems["supplier_{$i}"] = Action::make("nav_supplier_{$i}")
                ->label(fn (): string => self::getTenantLabel('suppliers', $idx, 'fournisseur'))
                ->icon('heroicon-o-cube')
                ->url(fn (): string => self::getTenantUrl('suppliers', $idx, 'supplier'))
                ->visible(fn (): bool => self::isTenantVisible('suppliers', $idx, 'supplier', $currentPanelId));
        }

        return array_values($menuItems);
    }

    /**
     * Get tenant label
     */
    private static function getTenantLabel(string $relation, int $index, string $prefix): string
    {
        $tenant = self::getUserWithRelations()?->{$relation}[$index] ?? null;

        return $tenant ? "{$prefix} {$tenant->name}" : '';
    }

    /**
     * Get tenant URL
     */
    private static function getTenantUrl(string $relation, int $index, string $panelPath): string
    {
        $tenant = self::getUserWithRelations()?->{$relation}[$index] ?? null;

        return $tenant ? "/{$panelPath}/{$tenant->id}" : "/{$panelPath}";
    }

    /**
     * Check if tenant is visible
     */
    private static function isTenantVisible(string $relation, int $index, string $panelId, string $currentPanelId): bool
    {
        $user = self::getUserWithRelations();
        if (! $user) {
            return false;
        }

        $tenant = $user->{$relation}[$index] ?? null;
        if (! $tenant) {
            return false;
        }

        // Don't show current tenant
        if ($currentPanelId === $panelId) {
            $currentTenant = Filament::getTenant();
            if ($currentTenant && $currentTenant->id === $tenant->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the current authenticated user
     */
    private static function getUser(): ?User
    {
        return Filament::auth()->user();
    }

    /**
     * Get user with all tenant relationships loaded
     *
     * Uses withoutGlobalScopes() to prevent Filament's tenant scoping
     * from affecting the tenant list query
     */
    private static function getUserWithRelations(): ?User
    {
        static $cachedUser = null;
        static $cacheKey = null;

        $user = self::getUser();
        if (! $user) {
            return null;
        }

        // Use fresh query with withoutGlobalScopes on relationships
        // to ensure all tenants are loaded regardless of current tenant context
        if ($cachedUser === null || $cacheKey !== $user->id) {
            $cachedUser = User::withoutGlobalScopes()
                ->with([
                    'shops' => fn ($q) => $q->withoutGlobalScopes(),
                    'kitchens' => fn ($q) => $q->withoutGlobalScopes(),
                    'drivers' => fn ($q) => $q->withoutGlobalScopes(),
                    'supervisors' => fn ($q) => $q->withoutGlobalScopes(),
                    'suppliers' => fn ($q) => $q->withoutGlobalScopes(),
                ])
                ->find($user->id);
            $cacheKey = $user->id;
        }

        return $cachedUser;
    }
}
