<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8),
                        Forms\Components\Select::make('primary_role_id')
                            ->label('Primary Role')
                            ->relationship('primaryRole', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('primaryRole.name')
                    ->label('Primary Role')
                    ->badge()
                    ->color(fn (User $record) => $record->primaryRole?->color ?? 'gray'),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Additional Roles'),
                Tables\Columns\TextColumn::make('shops_count')
                    ->counts('shops')
                    ->label('Shops'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->counts('kitchens')
                    ->label('Kitchens'),
                Tables\Columns\TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('Drivers'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('primary_role_id')
                    ->relationship('primaryRole', 'name')
                    ->label('Primary Role'),
                Tables\Filters\Filter::make('has_shops')
                    ->query(fn (Builder $query): Builder => $query->has('shops'))
                    ->label('Has Shops'),
                Tables\Filters\Filter::make('has_kitchens')
                    ->query(fn (Builder $query): Builder => $query->has('kitchens'))
                    ->label('Has Kitchens'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RolesRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
            RelationManagers\ShopsRelationManager::class,
            RelationManagers\KitchensRelationManager::class,
            RelationManagers\DriversRelationManager::class,
            RelationManagers\SuppliersRelationManager::class,
            RelationManagers\SupervisorsRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
