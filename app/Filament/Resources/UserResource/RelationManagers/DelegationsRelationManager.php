<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\PermissionDelegation;
use Filament\Actions\ViewAction;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/**
 * DelegationsRelationManager
 *
 * View user delegations received (readonly)
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class DelegationsRelationManager extends RelationManager
{
    protected static string $relationship = 'delegationsReceived';

    protected static ?string $title = 'Delegations Received';

    /**
     * Form (not used - readonly)
     */
    public function form(Schema $form): Schema
    {
        return $form->component([]);
    }

    /**
     * Table for viewing delegations
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('permission.name')
            ->columns([
                Tables\Columns\TextColumn::make('delegator.name')
                    ->label('Delegated By')
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'revoked' => 'Revoked',
                    ])
                    ->query(function (Builder $query, $state) {
                        if ($state['value'] === 'active') {
                            return $query->where('valid_until', '>', now())
                                ->whereNull('revoked_at');
                        } elseif ($state['value'] === 'expired') {
                            return $query->where('valid_until', '<=', now())
                                ->whereNull('revoked_at');
                        } elseif ($state['value'] === 'revoked') {
                            return $query->whereNotNull('revoked_at');
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Delegation Details')
                    ->modalContent(fn (PermissionDelegation $record): View => view(
                        'filament.modals.delegation-details',
                        ['record' => $record],
                    )),
            ]);
    }
}
