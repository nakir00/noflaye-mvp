<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Shops\Pages;

use App\Filament\Admin\Clusters\Business\Resources\Shops\ShopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShop extends EditRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
