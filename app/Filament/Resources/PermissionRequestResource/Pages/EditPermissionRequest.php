<?php

namespace App\Filament\Resources\PermissionRequestResource\Pages;

use App\Filament\Resources\PermissionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * EditPermissionRequest
 *
 * Edit page for PermissionRequest resource
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class EditPermissionRequest extends EditRecord
{
    protected static string $resource = PermissionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
