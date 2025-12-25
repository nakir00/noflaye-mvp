<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use App\Models\PermissionGroup;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $title = 'Permissions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissionGroup.name')
                    ->label('Group')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('action_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'read' => 'info',
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('permission_group_id')
                    ->relationship('permissionGroup', 'name')
                    ->label('Group'),
                Tables\Filters\SelectFilter::make('action_type')
                    ->options([
                        'read' => 'Read',
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                    ]),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Add Permissions'),

                // Bulk Assign Action
                Action::make('bulk_assign')
                    ->label('Bulk Assign by Group')
                    ->icon('heroicon-o-squares-plus')
                    ->color('success')
                    ->schema(function () {
                        $groups = PermissionGroup::with('permissions')->get();

                        return $groups->map(function ($group) {
                            return CheckboxList::make('group_' . $group->id)
                                ->label($group->name)
                                ->options($group->permissions->pluck('name', 'id')->toArray())
                                ->columns(2);
                        })->toArray();
                    })
                    ->action(function (array $data, $livewire) {
                        $permissionIds = collect($data)
                            ->filter(fn ($value, $key) => str_starts_with($key, 'group_'))
                            ->flatten()
                            ->filter()
                            ->unique()
                            ->toArray();

                        $livewire->ownerRecord->permissions()->syncWithoutDetaching($permissionIds);

                        Notification::make()
                            ->title('Permissions added successfully')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('4xl'),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No permissions assigned')
            ->emptyStateDescription('Use "Bulk Assign by Group" for quick setup')
            ->emptyStateIcon('heroicon-o-key');
    }
}
