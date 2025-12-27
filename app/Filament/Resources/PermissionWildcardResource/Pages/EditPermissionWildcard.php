<?php

namespace App\Filament\Resources\PermissionWildcardResource\Pages;

use App\Filament\Resources\PermissionWildcardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermissionWildcard extends EditRecord
{
    protected static string $resource = PermissionWildcardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
