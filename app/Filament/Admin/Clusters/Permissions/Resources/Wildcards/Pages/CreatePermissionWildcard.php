<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Wildcards\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Wildcards\PermissionWildcardResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermissionWildcard extends CreateRecord
{
    protected static string $resource = PermissionWildcardResource::class;
}
