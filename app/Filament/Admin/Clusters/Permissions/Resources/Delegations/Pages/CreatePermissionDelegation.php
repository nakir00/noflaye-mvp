<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\Delegations\Pages;

use App\Filament\Admin\Clusters\Permissions\Resources\Delegations\PermissionDelegationResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * CreatePermissionDelegation
 *
 * Create page for PermissionDelegation resource
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class CreatePermissionDelegation extends CreateRecord
{
    protected static string $resource = PermissionDelegationResource::class;
}
