<?php

namespace App\Filament\Supervisor\Clusters\Management\Resources\Supervisors\Pages;

use App\Filament\Supervisor\Clusters\Management\Resources\Supervisors\SupervisorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupervisors extends ListRecords
{
    protected static string $resource = SupervisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
