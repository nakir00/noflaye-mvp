<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Delegations\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Delegations\PermissionDelegationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * ListPermissionDelegations
 *
 * List page for PermissionDelegation resource
 *
 * @author Noflaye Box Team
 *
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
