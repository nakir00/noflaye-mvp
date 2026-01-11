<?php

namespace App\Filament\Driver\Clusters\Profile\Resources\Drivers\Pages;

use App\Filament\Driver\Clusters\Profile\Resources\Drivers\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDriver extends EditRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
