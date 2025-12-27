<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionTemplateResource\Pages;
use App\Models\PermissionTemplate;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * PermissionTemplateResource
 *
 * Filament resource for managing permission templates
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionTemplateResource extends Resource
{
    protected static ?string $model = PermissionTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Basic Information')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                $set('slug', \Illuminate\Support\Str::slug($state))
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

                Section::make('Hierarchy & Scope')
                    ->components([
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Template')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('scope_id')
                            ->label('Scope')
                            ->relationship('scope', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Appearance')
                    ->components([
                        Forms\Components\ColorPicker::make('color')
                            ->required()
                            ->default('#3B82F6'),

                        Forms\Components\TextInput::make('icon')
                            ->required()
                            ->default('heroicon-o-shield-check')
                            ->placeholder('heroicon-o-shield-check')
                            ->helperText('Use Heroicons format'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Permissions')
                    ->components([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ]),

                Section::make('Wildcards')
                    ->components([
                        Forms\Components\CheckboxList::make('wildcards')
                            ->relationship('wildcards', 'pattern')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ]),

                Section::make('Options')
                    ->components([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('is_system')
                            ->label('System Template')
                            ->default(false)
                            ->inline(false)
                            ->helperText('System templates cannot be deleted'),

                        Forms\Components\Toggle::make('auto_sync_users')
                            ->label('Auto Sync Users')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Automatically sync permissions to assigned users'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
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
                    ->placeholder('None'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('wildcards_count')
                    ->counts('wildcards')
                    ->label('Wildcards')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_system')
                    ->label('System')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Template')
                    ->relationship('parent', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('System Template'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (PermissionTemplate $record) {
                        if ($record->is_system) {
                            throw new \Exception('Cannot delete system template');
                        }
                        if ($record->users()->count() > 0) {
                            throw new \Exception('Cannot delete template with assigned users');
                        }
                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionTemplates::route('/'),
            'create' => Pages\CreatePermissionTemplate::route('/create'),
            'edit' => Pages\EditPermissionTemplate::route('/{record}/edit'),
        ];
    }
}
