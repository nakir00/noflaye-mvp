<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Requests\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Requests\PermissionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * ListPermissionRequests
 *
 * List page for PermissionRequest resource
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class ListPermissionRequests extends ListRecords
{
    protected static string $resource = PermissionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
