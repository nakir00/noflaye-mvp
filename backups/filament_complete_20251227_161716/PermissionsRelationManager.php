<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Permission;
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
 * PermissionsRelationManager
 *
 * Manage user permissions
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $title = 'Direct Permissions';

    /**
     * Form for attaching permissions
     */
    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Forms\Components\Select::make('recordId')
                    ->label('Permission')
                    ->options(Permission::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('scope_id')
                    ->label('Scope')
                    ->options(Scope::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Global'),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->nullable()
                    ->minDate(now()),

                Forms\Components\Select::make('source')
                    ->label('Source')
                    ->options([
                        'direct' => 'Direct',
                        'template' => 'Template',
                        'wildcard' => 'Wildcard',
                        'inherited' => 'Inherited',
                    ])
                    ->default('direct')
                    ->required(),

                Forms\Components\KeyValue::make('conditions')
                    ->label('Conditions')
                    ->nullable()
                    ->helperText('JSON conditions for permission'),
            ]);
    }

    /**
     * Table for managing permissions
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

                Tables\Columns\TextColumn::make('pivot.scope.name')
                    ->label('Scope')
                    ->placeholder('Global')
                    ->badge(),

                Tables\Columns\TextColumn::make('pivot.source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'direct' => 'primary',
                        'template' => 'success',
                        'wildcard' => 'warning',
                        'inherited' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('pivot.expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->placeholder('Never'),

                Tables\Columns\IconColumn::make('pivot.conditions')
                    ->label('Has Conditions')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->pivot->conditions)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'direct' => 'Direct',
                        'template' => 'Template',
                        'wildcard' => 'Wildcard',
                        'inherited' => 'Inherited',
                    ]),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('scope_id')
                            ->label('Scope')
                            ->options(Scope::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->minDate(now()),
                        Forms\Components\Select::make('source')
                            ->options([
                                'direct' => 'Direct',
                                'template' => 'Template',
                                'wildcard' => 'Wildcard',
                            ])
                            ->default('direct'),
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
