<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

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

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Forms\Components\Select::make('role_id')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('scope_type')
                    ->label('Scope Type')
                    ->options([
                        'global' => 'Global',
                        'shop' => 'Shop',
                        'kitchen' => 'Kitchen',
                        'driver' => 'Driver',
                        'supplier' => 'Supplier',
                        'supervisor' => 'Supervisor',
                    ])
                    ->reactive()
                    ->nullable()
                    ->helperText('Leave empty for global scope'),
                Forms\Components\TextInput::make('scope_id')
                    ->label('Scope Entity ID')
                    ->numeric()
                    ->nullable()
                    ->visible(fn (callable $get) => filled($get('scope_type')) && $get('scope_type') !== 'global')
                    ->helperText('ID of the specific entity (shop, kitchen, etc.)'),
                Forms\Components\DateTimePicker::make('valid_from')
                    ->label('Valid From')
                    ->default(now())
                    ->nullable(),
                Forms\Components\DateTimePicker::make('valid_until')
                    ->label('Valid Until')
                    ->nullable()
                    ->after('valid_from')
                    ->helperText('Leave empty for unlimited validity'),
                Forms\Components\Textarea::make('reason')
                    ->rows(2)
                    ->helperText('Why is this role being assigned?'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->color(fn ($record) => $record->color ?? 'gray'),
                Tables\Columns\TextColumn::make('pivot.scope_type')
                    ->label('Scope')
                    ->badge()
                    ->color(fn (string $state = null): string => match ($state) {
                        'global' => 'gray',
                        'shop' => 'primary',
                        'kitchen' => 'warning',
                        'driver' => 'success',
                        'supplier' => 'info',
                        'supervisor' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Global'),
                Tables\Columns\TextColumn::make('pivot.scope_id')
                    ->label('Scope ID')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pivot.valid_from')
                    ->label('Valid From')
                    ->dateTime()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pivot.valid_until')
                    ->label('Valid Until')
                    ->dateTime()
                    ->placeholder('Unlimited')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pivot.reason')
                    ->label('Reason')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scope_type')
                    ->label('Scope Type')
                    ->options([
                        'global' => 'Global',
                        'shop' => 'Shop',
                        'kitchen' => 'Kitchen',
                        'driver' => 'Driver',
                        'supplier' => 'Supplier',
                        'supervisor' => 'Supervisor',
                    ])
                    ->modifyQueryUsing(fn ($query, $state) =>
                        $state ? $query->wherePivot('scope_type', $state) : $query
                    ),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('scope_type')
                            ->label('Scope Type')
                            ->options([
                                'global' => 'Global',
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->reactive()
                            ->nullable()
                            ->helperText('Leave empty for global scope'),
                        Forms\Components\TextInput::make('scope_id')
                            ->label('Scope Entity ID')
                            ->numeric()
                            ->nullable()
                            ->visible(fn (callable $get) => filled($get('scope_type')) && $get('scope_type') !== 'global')
                            ->helperText('ID of the specific entity'),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Valid From')
                            ->default(now())
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Valid Until')
                            ->nullable()
                            ->helperText('Leave empty for unlimited'),
                        Forms\Components\Hidden::make('granted_by')
                            ->default(auth()->id()),
                        Forms\Components\Textarea::make('reason')
                            ->rows(2)
                            ->helperText('Why is this role being assigned?'),
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
