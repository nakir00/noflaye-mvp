<?php

namespace App\Filament\Driver\Clusters\Deliveries;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;

class DeliveriesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'My Deliveries';

    protected static string|UnitEnum|null $navigationGroup = 'Deliveries';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getClusterBreadcrumb(): string
    {
        return __('Deliveries');
    }
}
