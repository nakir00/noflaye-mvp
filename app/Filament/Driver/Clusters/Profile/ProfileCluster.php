<?php

namespace App\Filament\Driver\Clusters\Profile;

use App\Models\Driver;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;

class ProfileCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Profile';

    protected static string|UnitEnum|null $navigationGroup = 'Profile';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getClusterBreadcrumb(): ?string
    {
        return 'Profile';
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}
