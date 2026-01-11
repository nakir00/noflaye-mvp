<?php

namespace App\Filament\Admin\Clusters\Permissions;

use App\Models\PermissionRequest;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;

class PermissionsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Permissions';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions & Security';

    protected static ?int $navigationSort = 10;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = PermissionRequest::where('status', 'pending')->count();

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = PermissionRequest::where('status', 'pending')->count();

        if ($count > 10) {
            return 'danger';
        }

        if ($count > 5) {
            return 'warning';
        }

        return 'success';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = PermissionRequest::where('status', 'pending')->count();

        return $count > 0 ? "{$count} pending permission request(s)" : null;
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Permissions System');
    }
}
