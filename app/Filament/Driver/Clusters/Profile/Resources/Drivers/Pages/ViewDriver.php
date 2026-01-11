<?php

namespace App\Filament\Driver\Clusters\Profile\Resources\Drivers\Pages;

use App\Filament\Driver\Clusters\Profile\Resources\Drivers\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
