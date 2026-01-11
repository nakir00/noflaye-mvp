<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Suppliers\Pages;

use App\Filament\Admin\Clusters\Business\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
}
