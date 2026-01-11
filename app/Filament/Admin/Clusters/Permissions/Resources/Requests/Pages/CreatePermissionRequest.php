<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Requests\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Requests\PermissionRequestResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * CreatePermissionRequest
 *
 * Create page for PermissionRequest resource
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class CreatePermissionRequest extends CreateRecord
{
    protected static string $resource = PermissionRequestResource::class;
}
