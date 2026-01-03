<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionDelegationResource\Pages;
use App\Models\PermissionDelegation;
use App\Services\Permissions\PermissionDelegator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * PermissionDelegationResource
 *
 * Filament resource for managing permission delegations
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionDelegationResource extends Resource
{
    protected static ?string $model = PermissionDelegation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-right-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Delegation Information')
                    ->components([
                        Forms\Components\Select::make('delegator_id')
                            ->label('Delegator')
                            ->relationship('delegator', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('delegatee_id')
                            ->label('Delegatee')
                            ->relationship('delegatee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('permission_id')
                            ->label('Permission')
                            ->relationship('permission', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('scope_id')
                            ->label('Scope')
                            ->relationship('scope', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($context) => $context === 'edit'),
                    ])
                    ->columns(2),

                Section::make('Validity Period')
                    ->components([
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->required()
                            ->default(now())
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\DateTimePicker::make('valid_until')
                            ->required()
                            ->after('valid_from')
                            ->minDate(now()),
                    ])
                    ->columns(2),

                Section::make('Re-delegation Options')
                    ->components([
                        Forms\Components\Toggle::make('can_redelegate')
                            ->label('Can Re-delegate')
                            ->default(false)
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\TextInput::make('max_redelegation_depth')
                            ->label('Max Re-delegation Depth')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(5)
                            ->disabled(fn ($context) => $context === 'edit'),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->components([
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->disabled(fn ($context) => $context === 'edit'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delegator.name')
                    ->label('Delegator')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('delegatee.name')
                    ->label('Delegatee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permission.name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('scope.name')
                    ->label('Scope')
                    ->searchable()
                    ->placeholder('Global'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->state(function (PermissionDelegation $record): string {
                        if ($record->revoked_at) {
                            return 'revoked';
                        }
                        if ($record->valid_until < now()) {
                            return 'expired';
                        }

                        return 'active';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'warning',
                        'revoked' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('valid_until')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('can_redelegate')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('delegator_id')
                    ->label('Delegator')
                    ->relationship('delegator', 'name'),

                Tables\Filters\SelectFilter::make('delegatee_id')
                    ->label('Delegatee')
                    ->relationship('delegatee', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'revoked' => 'Revoked',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'active') {
                            return $query->active();
                        } elseif ($state['value'] === 'expired') {
                            return $query->expired();
                        } elseif ($state['value'] === 'revoked') {
                            return $query->revoked();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('revocation_reason')
                            ->label('Revocation Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (PermissionDelegation $record, array $data) {
                        $delegator = app(PermissionDelegator::class);
                        $delegator->revoke($record, Auth::user(), $data['revocation_reason']);

                        \Filament\Notifications\Notification::make()
                            ->title('Delegation revoked')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PermissionDelegation $record) => $record->isActive()),

                Action::make('extend')
                    ->label('Extend')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->schema([
                        DateTimePicker::make('new_expiration')
                            ->label('New Expiration Date')
                            ->required()
                            ->after(now())
                            ->minDate(now()),
                    ])
                    ->action(function (PermissionDelegation $record, array $data) {
                        $delegator = app(PermissionDelegator::class);
                        $delegator->extendDelegation($record, \Carbon\Carbon::parse($data['new_expiration']));

                        \Filament\Notifications\Notification::make()
                            ->title('Delegation extended')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PermissionDelegation $record) => $record->isActive()),

                DeleteAction::make()
                    ->visible(fn (PermissionDelegation $record) => ! $record->isActive()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('revoke_all')
                        ->label('Revoke All')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('revocation_reason')
                                ->label('Revocation Reason')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $delegator = app(PermissionDelegator::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->isActive()) {
                                    $delegator->revoke($record, Auth::user(), $data['revocation_reason']);
                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Revoked {$count} delegations")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionDelegations::route('/'),
            'create' => Pages\CreatePermissionDelegation::route('/create'),
            'edit' => Pages\EditPermissionDelegation::route('/{record}/edit'),
        ];
    }
}
