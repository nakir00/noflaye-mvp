<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\UserGroups\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\UserGroups\UserGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserGroup extends CreateRecord
{
    protected static string $resource = UserGroupResource::class;
}
