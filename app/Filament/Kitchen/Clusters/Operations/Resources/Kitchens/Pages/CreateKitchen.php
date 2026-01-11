<?php

namespace App\Filament\Kitchen\Clusters\Operations\Resources\Kitchens\Pages;

use App\Filament\Kitchen\Clusters\Operations\Resources\Kitchens\KitchenResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKitchen extends CreateRecord
{
    protected static string $resource = KitchenResource::class;
}
