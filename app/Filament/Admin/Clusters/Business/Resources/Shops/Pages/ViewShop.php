<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Shops\Pages;

use App\Filament\Admin\Clusters\Business\Resources\Shops\ShopResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewShop extends ViewRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
