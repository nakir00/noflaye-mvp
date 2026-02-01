<?php

namespace App\Filament\Shop\Clusters\Operations;

use BackedEnum;
use Filament\Clusters\Cluster;

class OperationsCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Operations';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;
}
