<?php

namespace App\Filament\Supplier\Clusters\Operations\Resources\Suppliers\Pages;

use App\Filament\Supplier\Clusters\Operations\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
}
