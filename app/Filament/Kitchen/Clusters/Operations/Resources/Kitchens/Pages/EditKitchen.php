<?php

namespace App\Filament\Kitchen\Clusters\Operations\Resources\Kitchens\Pages;

use App\Filament\Kitchen\Clusters\Operations\Resources\Kitchens\KitchenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKitchen extends EditRecord
{
    protected static string $resource = KitchenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
