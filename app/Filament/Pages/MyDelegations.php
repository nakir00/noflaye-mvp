<?php

namespace App\Filament\Pages;

use App\Models\PermissionDelegation;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * MyDelegations Page
 *
 * User's delegations (received and given)
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class MyDelegations extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-right-circle';

    protected string $view = 'filament.pages.my-delegations';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'My Delegations';

    /**
     * Table for received delegations
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PermissionDelegation::query()
                    ->where('delegatee_id', Auth::id())
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('delegator.name')
                    ->label('Delegated By')
                    ->searchable(),

                Tables\Columns\TextColumn::make('permission.name')
                    ->label('Permission')
                    ->limit(30),

                Tables\Columns\TextColumn::make('scope.name')
                    ->label('Scope')
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
                    ->dateTime(),

                Tables\Columns\IconColumn::make('can_redelegate')
                    ->boolean(),
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
                            return $query->active();
                        } elseif ($state['value'] === 'expired') {
                            return $query->expired();
                        } elseif ($state['value'] === 'revoked') {
                            return $query->revoked();
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

    /**
     * Get given delegations
     */
    public function getGivenDelegations()
    {
        return PermissionDelegation::where('delegator_id', Auth::id())
            ->with(['delegatee', 'permission', 'scope'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
