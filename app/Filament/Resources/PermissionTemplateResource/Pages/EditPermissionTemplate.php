<?php

namespace App\Filament\Resources\PermissionTemplateResource\Pages;

use App\Filament\Resources\PermissionTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermissionTemplate extends EditRecord
{
    protected static string $resource = PermissionTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
