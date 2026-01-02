<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionWildcardResource\Pages;
use App\Models\PermissionWildcard;
use App\Services\Permissions\WildcardExpander;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * PermissionWildcardResource
 *
 * Filament resource for managing permission wildcards
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionWildcardResource extends Resource
{
    protected static ?string $model = PermissionWildcard::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Wildcard Information')
                    ->components([
                        Forms\Components\TextInput::make('pattern')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('shops.* or *.read')
                            ->helperText('Use * for wildcards'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('pattern_type')
                            ->options([
                                'full' => 'Full (*.*)',
                                'resource' => 'Resource (shops.*)',
                                'action' => 'Action (*.read)',
                                'macro' => 'Macro (custom)',
                            ])
                            ->required()
                            ->default('resource'),
                    ])
                    ->columns(2),

                Section::make('Appearance')
                    ->components([
                        Forms\Components\TextInput::make('icon')
                            ->default('heroicon-o-sparkles'),

                        Forms\Components\ColorPicker::make('color')
                            ->default('#8B5CF6'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Section::make('Options')
                    ->components([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),

                        Forms\Components\Toggle::make('auto_expand')
                            ->default(true)
                            ->helperText('Automatically expand when permissions change'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pattern')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('pattern_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'full' => 'danger',
                        'resource' => 'success',
                        'action' => 'info',
                        'macro' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('auto_expand')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_expanded_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pattern_type'),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('auto_expand'),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('expand')
                    ->label('Expand Now')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (PermissionWildcard $record) {
                        $expander = app(WildcardExpander::class);
                        $count = $expander->rebuildExpansions($record);

                        \Filament\Notifications\Notification::make()
                            ->title("Expanded to {$count} permissions")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('expand_all')
                        ->label('Expand All')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $expander = app(WildcardExpander::class);
                            $total = 0;

                            foreach ($records as $record) {
                                $total += $expander->rebuildExpansions($record);
                            }

                            Notification::make()
                                ->title("Expanded to {$total} permissions")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionWildcards::route('/'),
            'create' => Pages\CreatePermissionWildcard::route('/create'),
            'edit' => Pages\EditPermissionWildcard::route('/{record}/edit'),
        ];
    }
}
