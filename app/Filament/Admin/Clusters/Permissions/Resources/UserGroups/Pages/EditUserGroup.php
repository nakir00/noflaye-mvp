<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\UserGroups\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\UserGroups\UserGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserGroup extends EditRecord
{
    protected static string $resource = UserGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
