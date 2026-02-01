<?php

namespace App\Filament\Admin\Clusters\Business\Resources\Suppliers;

use App\Filament\Admin\Clusters\Business\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Admin\Clusters\Business\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Admin\Clusters\Business\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Admin\Clusters\Business\Resources\Suppliers\Pages\ViewSupplier;
use App\Filament\Admin\Clusters\Business\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Admin\Clusters\Business\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Supplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = \App\Filament\Admin\Clusters\Business\BusinessCluster::class;

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'view' => ViewSupplier::route('/{record}'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
