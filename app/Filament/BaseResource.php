<?php

namespace App\Filament;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Base Resource for Filament
 *
 * Abstract base class that provides common functionality for all Filament resources
 * in the NoFlaye application. This class standardizes table actions, bulk actions,
 * and common behaviors across all resources.
 *
 * Features:
 * - Standard CRUD actions (View, Edit, Delete)
 * - Soft delete support with Restore and Force Delete
 * - Bulk actions for efficient data management
 * - Configurable default table settings
 * - Permission-aware action visibility
 *
 * Usage:
 * ```php
 * class ShopResource extends BaseResource
 * {
 *     protected static ?string $model = Shop::class;
 *     // ... resource implementation
 * }
 * ```
 *
 * @see Resource
 */
abstract class BaseResource extends Resource
{
    /**
     * Whether this resource supports soft deletes
     *
     * Override this property in child classes to enable soft delete features
     * (Restore and Force Delete actions)
     */
    protected static bool $supportsSoftDeletes = false;

    /**
     * Default records per page for table pagination
     *
     * Override in child classes to customize pagination
     */
    protected static int $defaultRecordsPerPage = 25;

    /**
     * Available pagination options
     *
     * Override in child classes to customize available page sizes
     *
     * @var array<int>
     */
    protected static array $paginationOptions = [10, 25, 50, 100];

    /**
     * Configure default table settings
     *
     * Applies standard actions and bulk actions to the table.
     * Child classes should call parent::configureTable() when overriding.
     *
     * @param  Table  $table  The table instance
     * @return Table The configured table
     *
     * @example
     * public static function table(Table $table): Table
     * {
     *     return static::configureTable($table)
     *         ->columns([...])
     *         ->filters([...]);
     * }
     */
    protected static function configureTable(Table $table): Table
    {
        return $table
            ->recordActions(static::getDefaultRecordActions())
            ->toolbarActions(static::getDefaultToolbarActions())
            ->defaultPaginationPageOption(static::$defaultRecordsPerPage)
            ->paginationPageOptions(static::$paginationOptions)
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistSortInSession();
    }

    /**
     * Get default record actions (row-level actions)
     *
     * Returns standard actions for each table row: View, Edit, Delete
     * If soft deletes are enabled, includes Restore and Force Delete
     *
     * Override in child classes to customize record actions
     *
     * @return array<\Filament\Actions\Action>
     *
     * @example
     * protected static function getDefaultRecordActions(): array
     * {
     *     return [
     *         ...parent::getDefaultRecordActions(),
     *         Action::make('custom')->icon('heroicon-o-star'),
     *     ];
     * }
     */
    protected static function getDefaultRecordActions(): array
    {
        $actions = [
            ViewAction::make(),
            EditAction::make(),
        ];

        if (static::$supportsSoftDeletes) {
            $actions[] = DeleteAction::make();
            $actions[] = RestoreAction::make();
            $actions[] = ForceDeleteAction::make();
        } else {
            $actions[] = DeleteAction::make();
        }

        return $actions;
    }

    /**
     * Get default toolbar actions (bulk actions)
     *
     * Returns bulk actions for selected records
     * If soft deletes are enabled, includes Restore and Force Delete bulk actions
     *
     * Override in child classes to customize bulk actions
     *
     * @return array<\Filament\Actions\BulkActionGroup>
     *
     * @example
     * protected static function getDefaultToolbarActions(): array
     * {
     *     return [
     *         BulkActionGroup::make([
     *             ...parent::getDefaultToolbarActions()[0]->getActions(),
     *             BulkAction::make('archive')->icon('heroicon-o-archive-box'),
     *         ]),
     *     ];
     * }
     */
    protected static function getDefaultToolbarActions(): array
    {
        $bulkActions = [
            DeleteBulkAction::make(),
        ];

        if (static::$supportsSoftDeletes) {
            $bulkActions[] = RestoreBulkAction::make();
            $bulkActions[] = ForceDeleteBulkAction::make();
        }

        return [
            BulkActionGroup::make($bulkActions),
        ];
    }

    /**
     * Get default table filters
     *
     * Returns common filters used across resources.
     * Override in child classes to add resource-specific filters.
     *
     * @return array<\Filament\Tables\Filters\BaseFilter>
     *
     * @example
     * public static function getDefaultFilters(): array
     * {
     *     return [
     *         ...parent::getDefaultFilters(),
     *         SelectFilter::make('category'),
     *     ];
     * }
     */
    protected static function getDefaultFilters(): array
    {
        return [];
    }

    /**
     * Get the slug (URI key) for this resource
     *
     * Override this method to customize the URL segment for the resource
     *
     * @return string The resource slug
     *
     * @example
     * // Default: shop-managers
     * // Custom override:
     * public static function getSlug(): string
     * {
     *     return 'managers';
     * }
     */
    public static function getSlug(): string
    {
        return parent::getSlug();
    }

    /**
     * Get the label for this resource
     *
     * Override this method to customize the display name
     *
     * @return string The resource label
     */
    public static function getLabel(): ?string
    {
        return parent::getLabel();
    }

    /**
     * Get the plural label for this resource
     *
     * Override this method to customize the plural display name
     *
     * @return string The plural resource label
     */
    public static function getPluralLabel(): ?string
    {
        return parent::getPluralLabel();
    }

    /**
     * Get the navigation badge (count) for this resource
     *
     * Override to show a count badge in the navigation menu
     *
     * @return string|null The badge content
     *
     * @example
     * public static function getNavigationBadge(): ?string
     * {
     *     return static::getModel()::count();
     * }
     */
    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    /**
     * Determine if this resource should be globally searchable
     *
     * Override to enable/disable global search for this resource
     *
     * @return bool Whether global search is enabled
     */
    public static function isGloballySearchable(): bool
    {
        return true;
    }

    /**
     * Get the number of records to show in global search results
     *
     * @return int Number of search results
     */
    public static function getGlobalSearchResultsLimit(): int
    {
        return 5;
    }
}
