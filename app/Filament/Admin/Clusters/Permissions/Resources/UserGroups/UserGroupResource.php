<?php

namespace App\Filament\Admin\Clusters\Permissions\Resources\UserGroups;

use App\Models\UserGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * UserGroupResource
 *
 * Filament resource for managing user groups with hierarchy support.
 * Supports:
 * - Hierarchical groups (parent/children)
 * - Permission inheritance from parent groups and templates
 * - Permission override (grant/deny) system
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class UserGroupResource extends Resource
{
    protected static ?string $model = UserGroup::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $cluster = \App\Filament\Admin\Clusters\Permissions\PermissionsCluster::class;

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Informations de base')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Hiérarchie')
                    ->components([
                        Forms\Components\Select::make('parent_id')
                            ->label('Groupe Parent')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre de tri')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Template de permissions')
                    ->description('Associer un template pour hériter automatiquement de ses permissions')
                    ->components([
                        Forms\Components\Select::make('permission_template_id')
                            ->label('Template')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('auto_sync_template')
                            ->label('Synchronisation automatique')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Mettre à jour automatiquement les permissions quand le template change'),
                    ])
                    ->columns(2),

                Section::make('Permissions héritées')
                    ->description('Permissions reçues du groupe parent et du template')
                    ->components([
                        Forms\Components\Placeholder::make('inherited_permissions_display')
                            ->label('')
                            ->content(function (?UserGroup $record): string {
                                if (! $record) {
                                    return 'Sauvegardez d\'abord pour voir les permissions héritées';
                                }

                                $inherited = $record->getInheritedPermissions();
                                $template = $record->getTemplatePermissions();

                                $lines = [];

                                if ($template->isNotEmpty()) {
                                    $lines[] = "📋 Du template ({$record->template?->name}):";
                                    foreach ($template as $perm) {
                                        $lines[] = "  • {$perm->name}";
                                    }
                                }

                                if ($inherited->isNotEmpty()) {
                                    $lines[] = '';
                                    $lines[] = '👥 Des groupes parents:';
                                    foreach ($inherited as $perm) {
                                        $lines[] = "  • {$perm->name}";
                                    }
                                }

                                if (empty($lines)) {
                                    return 'Aucune permission héritée (pas de parent ni de template)';
                                }

                                return implode("\n", $lines);
                            }),
                    ])
                    ->visible(fn (?UserGroup $record): bool => $record !== null && ($record->parent_id !== null || $record->permission_template_id !== null))
                    ->collapsed(),

                Section::make('Permissions accordées')
                    ->description('Permissions directement attribuées à ce groupe')
                    ->components([
                        Forms\Components\CheckboxList::make('grantedPermissions')
                            ->label('')
                            ->relationship(
                                name: 'grantedPermissions',
                                titleAttribute: 'name',
                            )
                            ->pivotData([
                                'permission_type' => 'grant',
                                'source' => 'direct',
                            ])
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ]),

                Section::make('Permissions refusées (Override)')
                    ->description('Permissions à bloquer (override les permissions héritées et du template)')
                    ->components([
                        Forms\Components\CheckboxList::make('deniedPermissions')
                            ->label('')
                            ->relationship(
                                name: 'deniedPermissions',
                                titleAttribute: 'name',
                            )
                            ->pivotData([
                                'permission_type' => 'deny',
                                'source' => 'direct',
                            ])
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ])
                    ->collapsed()
                    ->visible(fn (?UserGroup $record): bool => $record !== null && ($record->parent_id !== null || $record->permission_template_id !== null)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->badge(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Aucun'),

                Tables\Columns\TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Aucun'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Utilisateurs')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('auto_sync_template')
                    ->label('Auto sync')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Groupe Parent')
                    ->relationship('parent', 'name'),

                Tables\Filters\SelectFilter::make('permission_template_id')
                    ->label('Template')
                    ->relationship('template', 'name'),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (UserGroup $record) {
                        if ($record->users()->count() > 0) {
                            throw new \Exception('Impossible de supprimer un groupe avec des utilisateurs');
                        }
                        if ($record->children()->count() > 0) {
                            throw new \Exception('Impossible de supprimer un groupe avec des sous-groupes');
                        }
                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserGroups::route('/'),
            'create' => Pages\CreateUserGroup::route('/create'),
            'edit' => Pages\EditUserGroup::route('/{record}/edit'),
        ];
    }
}
