<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Suppliers\Pages;

use App\Filament\Admin\Clusters\Business\Resources\Suppliers\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
