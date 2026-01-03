<?php

namespace App\Filament\Widgets;

use App\Models\Permission;
use App\Models\PermissionDelegation;
use App\Models\PermissionRequest;
use App\Models\UserPermission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * PermissionStatsWidget
 *
 * Stats overview for permission system
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionStatsWidget extends BaseWidget
{
    /**
     * Get the stats for the widget
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Total Permissions', Permission::count())
                ->description('Active permissions in system')
                ->descriptionIcon('heroicon-o-key')
                ->color('primary'),

            Stat::make('Active Assignments', UserPermission::whereNull('expires_at')
                ->orWhere('expires_at', '>', now())
                ->count())
                ->description('Current user permissions')
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),

            Stat::make('Active Delegations', PermissionDelegation::where('valid_until', '>', now())
                ->whereNull('revoked_at')
                ->count())
                ->description('Currently delegated')
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('warning'),

            Stat::make('Pending Requests', PermissionRequest::where('status', 'pending')->count())
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
