<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Wildcards\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Wildcards\PermissionWildcardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissionWildcards extends ListRecords
{
    protected static string $resource = PermissionWildcardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
