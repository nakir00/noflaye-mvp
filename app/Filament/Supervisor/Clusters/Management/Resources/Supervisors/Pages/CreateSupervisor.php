<?php

namespace App\Filament\Supervisor\Clusters\Management\Resources\Supervisors\Pages;

use App\Filament\Supervisor\Clusters\Management\Resources\Supervisors\SupervisorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupervisor extends CreateRecord
{
    protected static string $resource = SupervisorResource::class;
}
