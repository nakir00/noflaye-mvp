<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

/**
 * PermissionAnalyticsDashboard Page
 *
 * Analytics dashboard for permission system
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionAnalyticsDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.permission-analytics-dashboard';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Permission Analytics';

    protected static ?string $navigationLabel = 'Analytics';

    /**
     * Get the header widgets for this page
     *
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PermissionStatsWidget::class,
        ];
    }

    /**
     * Get the widgets for this page
     *
     * @return array<class-string>
     */
    protected function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\PermissionGrowthChart::class,
            \App\Filament\Widgets\MostUsedPermissionsWidget::class,
            \App\Filament\Widgets\TemplateAdoptionWidget::class,
        ];
    }

    /**
     * Get the header widgets columns
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    /**
     * Get the widgets columns
     */
    public function getWidgetsColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
