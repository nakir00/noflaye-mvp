<?php

namespace App\Filament\Widgets;

use App\Models\Permission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * MostUsedPermissionsWidget
 *
 * Table showing most frequently assigned permissions
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class MostUsedPermissionsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    /**
     * Get the table for the widget
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Permission::query()
                    ->withCount('userPermissions')
                    ->orderBy('user_permissions_count', 'desc')
                    ->limit(10)
            )
            ->heading('Most Used Permissions')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('Group')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('user_permissions_count')
                    ->label('Assignments')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ]);
    }
}
