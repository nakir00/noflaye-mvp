<?php

namespace App\Filament\Kitchen\Clusters\Operations;

use App\Models\Kitchen;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;

class OperationsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationLabel = 'Operations';

    protected static string|UnitEnum|null $navigationGroup = 'Kitchen Management';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getClusterBreadcrumb(): ?string
    {
        return 'Operations';
    }

    public static function getNavigationBadge(): ?string
    {
        $activeCount = Kitchen::where('is_active', true)->count();
        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
