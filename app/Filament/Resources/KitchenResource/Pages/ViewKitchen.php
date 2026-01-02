<?php

namespace App\Filament\Resources\KitchenResource\Pages;

use App\Filament\Resources\KitchenResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKitchen extends ViewRecord
{
    protected static string $resource = KitchenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
