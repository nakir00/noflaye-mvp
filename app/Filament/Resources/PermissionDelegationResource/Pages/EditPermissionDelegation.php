<?php

namespace App\Filament\Resources\PermissionDelegationResource\Pages;

use App\Filament\Resources\PermissionDelegationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * EditPermissionDelegation
 *
 * Edit page for PermissionDelegation resource
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class EditPermissionDelegation extends EditRecord
{
    protected static string $resource = PermissionDelegationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
