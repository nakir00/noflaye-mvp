<?php

namespace App\Filament\Admin\Clusters\AccessControl\Resources\Users\RelationManagers;

use BackedEnum;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'templates';

    protected static ?string $title = 'Permission Templates';

    protected static string|BackedEnum|null $icon = 'heroicon-o-shield-check';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_system')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
                Tables\Columns\TextColumn::make('pivot.valid_from')
                    ->label('Valid From')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pivot.valid_until')
                    ->label('Valid Until')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'slug'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('valid_until'),
                        Forms\Components\Toggle::make('auto_upgrade')
                            ->default(true)
                            ->helperText('Automatically upgrade to newer template versions'),
                        Forms\Components\Toggle::make('auto_sync')
                            ->default(true)
                            ->helperText('Automatically sync permissions from template'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
