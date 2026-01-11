<?php

namespace App\Filament\Supervisor\Clusters\Management;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;

class ManagementCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Management';

    protected static string|UnitEnum|null $navigationGroup = 'Supervision';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getClusterBreadcrumb(): string
    {
        return __('Management');
    }
}
