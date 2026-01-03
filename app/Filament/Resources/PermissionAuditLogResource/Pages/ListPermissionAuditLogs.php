<?php

namespace App\Filament\Resources\PermissionAuditLogResource\Pages;

use App\Filament\Resources\PermissionAuditLogResource;
use Filament\Resources\Pages\ListRecords;

/**
 * ListPermissionAuditLogs
 *
 * List page for PermissionAuditLog resource (readonly)
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class ListPermissionAuditLogs extends ListRecords
{
    protected static string $resource = PermissionAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for audit logs
        ];
    }
}
