<?php

namespace App\Filament\Resources\PermissionDelegationResource\Pages;

use App\Filament\Resources\PermissionDelegationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * ListPermissionDelegations
 *
 * List page for PermissionDelegation resource
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class ListPermissionDelegations extends ListRecords
{
    protected static string $resource = PermissionDelegationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
