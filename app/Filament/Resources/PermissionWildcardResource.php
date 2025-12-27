<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionWildcardResource\Pages;
use App\Models\PermissionWildcard;
use App\Services\Permissions\WildcardExpander;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * PermissionWildcardResource
 *
 * Filament resource for managing permission wildcards
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionWildcardResource extends Resource
{
    protected static ?string $model = PermissionWildcard::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Wildcard Information')
                    ->schema([
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

                Forms\Components\Section::make('Appearance')
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->default('heroicon-o-sparkles'),

                        Forms\Components\ColorPicker::make('color')
                            ->default('#8B5CF6'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Options')
                    ->schema([
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
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('expand')
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

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('expand_all')
                        ->label('Expand All')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $expander = app(WildcardExpander::class);
                            $total = 0;

                            foreach ($records as $record) {
                                $total += $expander->rebuildExpansions($record);
                            }

                            \Filament\Notifications\Notification::make()
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
