<?php

namespace App\Filament\Shop\Clusters\Operations\Resources\Shops\Pages;

use App\Filament\Shop\Clusters\Operations\Resources\Shops\ShopResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShop extends EditRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
