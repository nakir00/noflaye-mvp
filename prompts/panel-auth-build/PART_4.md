SupervisorResource (Admin Panel)

php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervisorResource\Pages;
use App\Filament\Resources\SupervisorResource\RelationManagers;
use App\Models\Supervisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupervisorResource extends Resource
{
    protected static ?string $model = Supervisor::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Entities Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Supervisor Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        Forms\Components\Textarea::make('address')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
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
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Managers'),
                Tables\Columns\TextColumn::make('shops_count')
                    ->counts('shops')
                    ->label('Shops'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->counts('kitchens')
                    ->label('Kitchens'),
                Tables\Columns\TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('Drivers'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\ShopsRelationManager::class,
            RelationManagers\KitchensRelationManager::class,
            RelationManagers\DriversRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupervisors::route('/'),
            'create' => Pages\CreateSupervisor::route('/create'),
            'view' => Pages\ViewSupervisor::route('/{record}'),
            'edit' => Pages\EditSupervisor::route('/{record}/edit'),
        ];
    }
}DefaultPermissionTemplateResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DefaultPermissionTemplateResource\Pages;
use App\Filament\Resources\DefaultPermissionTemplateResource\RelationManagers;
use App\Models\DefaultPermissionTemplate;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Supplier;
use App\Models\Supervisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DefaultPermissionTemplateResource extends Resource
{
    protected static ?string $model = DefaultPermissionTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 4;

    protected static ?string $label = 'Permission Template';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\Select::make('scope_type')
                            ->options([
                                'global' => 'Global',
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->nullable()
                            ->reactive(),
                        Forms\Components\Select::make('scope_id')
                            ->options(function (callable $get) {
                                $type = $get('scope_type');
                                return match ($type) {
                                    'shop' => Shop::pluck('name', 'id'),
                                    'kitchen' => Kitchen::pluck('name', 'id'),
                                    'supplier' => Supplier::pluck('name', 'id'),
                                    'supervisor' => Supervisor::pluck('name', 'id'),
                                    default => [],
                                };
                            })
                            ->visible(fn (callable $get) => filled($get('scope_type')) && $get('scope_type') !== 'global')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Set as Default Template for Scope')
                            ->helperText('This template will be auto-applied when inviting users'),
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
                Tables\Columns\TextColumn::make('scope_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'global' => 'success',
                        'shop' => 'primary',
                        'kitchen' => 'warning',
                        'driver' => 'info',
                        'supplier' => 'purple',
                        'supervisor' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Roles'),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
                Tables\Columns\TextColumn::make('userGroups_count')
                    ->counts('userGroups')
                    ->label('Groups'),
                Tables\Columns\IconColumn::make('is_default')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RolesRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $query;
        }

        // Filter by user's managed entities
        $panel = filament()->getCurrentPanel()->getId();

        return $query->where(function ($q) use ($user, $panel) {
            $q->whereNull('scope_type')
              ->orWhere(function ($sub) use ($user, $panel) {
                  $sub->where('scope_type', $panel);

                  $method = 'getManaged' . ucfirst($panel) . 's';
                  if (method_exists($user, $method)) {
                      $scopeIds = $user->$method()->pluck('id');
                      $sub->whereIn('scope_id', $scopeIds);
                  }
              });
        });
    }
}📦 RELATION MANAGERS DÉTAILLÉSRolesRelationManager (pour User)php<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $title = 'Roles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('recordId')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->color(fn ($record) => $record->color ?? 'gray')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.scope_type')
                    ->label('Scope Type')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('pivot.scope_id')
                    ->label('Scope ID'),
                Tables\Columns\TextColumn::make('pivot.valid_from')
                    ->label('Valid From')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('pivot.valid_until')
                    ->label('Valid Until')
                    ->dateTime()
                    ->placeholder('Forever'),
                Tables\Columns\TextColumn::make('pivot.reason')
                    ->label('Reason')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->pivot->reason),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('scope_type')
                            ->options([
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->nullable()
                            ->reactive(),
                        Forms\Components\Select::make('scope_id')
                            ->options(function (callable $get) {
                                $scopeType = $get('scope_type');
                                if (!$scopeType) return [];

                                $modelClass = 'App\\Models\\' . ucfirst($scopeType);
                                if (!class_exists($modelClass)) return [];

                                return $modelClass::pluck('name', 'id');
                            })
                            ->visible(fn (callable $get) => filled($get('scope_type')))
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->nullable(),
                        Forms\Components\Textarea::make('reason')
                            ->rows(3)
                            ->placeholder('Why is this role being assigned?'),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['granted_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No roles assigned')
            ->emptyStateDescription('Assign roles using the button above.')
            ->emptyStateIcon('heroicon-o-shield-check');
    }
}PermissionsRelationManager (pour User)php<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $title = 'Direct Permissions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.permission_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'grant' => 'success',
                        'revoke' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pivot.scope_type')
                    ->label('Scope Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('pivot.valid_from')
                    ->label('Valid From')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('pivot.reason')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->pivot->reason),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('permission_type')
                            ->options([
                                'grant' => 'Grant',
                                'revoke' => 'Revoke',
                            ])
                            ->default('grant')
                            ->required(),
                        Forms\Components\Select::make('scope_type')
                            ->options([
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->nullable()
                            ->reactive(),
                        Forms\Components\Select::make('scope_id')
                            ->options(function (callable $get) {
                                $scopeType = $get('scope_type');
                                if (!$scopeType) return [];

                                $modelClass = 'App\\Models\\' . ucfirst($scopeType);
                                return $modelClass::pluck('name', 'id');
                            })
                            ->visible(fn (callable $get) => filled($get('scope_type')))
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->nullable(),
                        Forms\Components\Textarea::make('reason')
                            ->rows(3),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['granted_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
