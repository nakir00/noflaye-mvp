<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\PermissionTemplate;
use App\Models\Scope;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * TemplatesRelationManager
 *
 * Manage user permission templates
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class TemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'templates';

    protected static ?string $title = 'Permission Templates';

    /**
     * Form for attaching templates
     */
    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Forms\Components\Select::make('recordId')
                    ->label('Template')
                    ->options(PermissionTemplate::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('scope_id')
                    ->label('Scope')
                    ->options(Scope::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Global'),

                Forms\Components\Toggle::make('auto_sync')
                    ->label('Auto Sync')
                    ->helperText('Automatically sync permissions when template is updated')
                    ->default(true),
            ]);
    }

    /**
     * Table for managing templates
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->badge(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('pivot.scope.name')
                    ->label('Scope')
                    ->placeholder('Global')
                    ->badge(),

                Tables\Columns\IconColumn::make('pivot.auto_sync')
                    ->label('Auto Sync')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('auto_sync')
                    ->label('Auto Sync'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Templates'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('scope_id')
                            ->label('Scope')
                            ->options(Scope::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Toggle::make('auto_sync')
                            ->label('Auto Sync')
                            ->default(true),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
