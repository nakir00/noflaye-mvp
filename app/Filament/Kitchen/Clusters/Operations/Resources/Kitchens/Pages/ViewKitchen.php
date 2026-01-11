<?php

namespace App\Filament\Kitchen\Clusters\Operations\Resources\Kitchens\Pages;

use App\Filament\Kitchen\Clusters\Operations\Resources\Kitchens\KitchenResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKitchen extends ViewRecord
{
    protected static string $resource = KitchenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
