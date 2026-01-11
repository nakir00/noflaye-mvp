<?php

namespace App\Filament\Driver\Clusters\Profile\Resources\Drivers\Pages;

use App\Filament\Driver\Clusters\Profile\Resources\Drivers\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrivers extends ListRecords
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
