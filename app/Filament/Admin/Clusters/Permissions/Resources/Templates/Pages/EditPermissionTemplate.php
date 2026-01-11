<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Templates\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Templates\PermissionTemplateResource;
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
