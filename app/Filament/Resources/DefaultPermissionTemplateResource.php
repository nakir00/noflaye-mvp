<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DefaultPermissionTemplateResource\Pages;
use App\Filament\Resources\DefaultPermissionTemplateResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\PermissionsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\RolesRelationManager;
use App\Models\DefaultPermissionTemplate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use UnitEnum;

class DefaultPermissionTemplateResource extends Resource
{
    protected static ?string $model = DefaultPermissionTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static UnitEnum|string|null $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Permission Templates';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Template Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                        Select::make('scope_type')
                            ->options([
                                'global' => 'Global',
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->nullable()
                            ->reactive()
                            ->helperText('Leave empty for global template'),
                        TextInput::make('scope_id')
                            ->numeric()
                            ->nullable()
                            ->visible(fn (callable $get) => filled($get('scope_type')) && $get('scope_type') !== 'global')
                            ->helperText('ID of the specific entity (shop, kitchen, etc.)'),
                        Toggle::make('is_default')
                            ->label('Set as Default Template')
                            ->helperText('Auto-apply to new users in this scope')
                            ->default(false),
                        Toggle::make('is_active')
                            ->default(true),
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
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scope_type')
                    ->badge()
                    ->color(fn (?string $state = null): string => match ($state) {
                        'global' => 'gray',
                        'shop' => 'primary',
                        'kitchen' => 'warning',
                        'driver' => 'success',
                        'supplier' => 'info',
                        'supervisor' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Global'),
                Tables\Columns\TextColumn::make('scope_id')
                    ->label('Scope Entity ID')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Roles'),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
                Tables\Columns\TextColumn::make('userGroups_count')
                    ->counts('userGroups')
                    ->label('User Groups'),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scope_type')
                    ->options([
                        'global' => 'Global',
                        'shop' => 'Shop',
                        'kitchen' => 'Kitchen',
                        'driver' => 'Driver',
                        'supplier' => 'Supplier',
                        'supervisor' => 'Supervisor',
                    ]),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Template'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('apply_to_user')
                    ->label('Apply to User')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('users', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('scope_type')
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
                            ->nullable(),
                        TextInput::make('scope_id')
                            ->label('Scope ID')
                            ->numeric()
                            ->nullable()
                            ->visible(fn (callable $get) => filled($get('scope_type')) && $get('scope_type') !== 'global'),
                    ])
                    ->action(function (DefaultPermissionTemplate $record, array $data): void {
                        $user = \App\Models\User::find($data['user_id']);
                        $record->applyToUser(
                            $user,
                            $data['scope_type'] ?? null,
                            $data['scope_id'] ?? null
                        );
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Template Applied')
                            ->body('The template has been successfully applied to the user.')
                    ),
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
            RolesRelationManager::class,
            PermissionsRelationManager::class,
            UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDefaultPermissionTemplates::route('/'),
            'create' => Pages\CreateDefaultPermissionTemplate::route('/create'),
            'view' => Pages\ViewDefaultPermissionTemplate::route('/{record}'),
            'edit' => Pages\EditDefaultPermissionTemplate::route('/{record}/edit'),
        ];
    }
}
