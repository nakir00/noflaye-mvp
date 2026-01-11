<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Templates\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Templates\PermissionTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermissionTemplate extends CreateRecord
{
    protected static string $resource = PermissionTemplateResource::class;
}
