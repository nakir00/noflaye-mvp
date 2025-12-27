<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionRequestResource\Pages;
use App\Models\PermissionRequest;
use App\Services\Permissions\PermissionApprovalWorkflow;
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
 * PermissionRequestResource
 *
 * Filament resource for managing permission requests
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionRequestResource extends Resource
{
    protected static ?string $model = PermissionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Request Information')
                    ->components([
                        Forms\Components\Select::make('user_id')
                            ->label('Requester')
                            ->relationship('user', 'name')
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

                Section::make('Request Details')
                    ->components([
                        Forms\Components\Textarea::make('reason')
                            ->label('Request Reason')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->disabled(),
                    ]),

                Section::make('Validity Period')
                    ->components([
                        Forms\Components\DateTimePicker::make('requested_from')
                            ->label('Valid From')
                            ->default(now())
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\DateTimePicker::make('requested_until')
                            ->label('Valid Until')
                            ->after('requested_from')
                            ->disabled(fn ($context) => $context === 'edit'),
                    ])
                    ->columns(2),

                Section::make('Review Information')
                    ->components([
                        Forms\Components\Select::make('reviewed_by')
                            ->label('Reviewed By')
                            ->relationship('reviewer', 'name')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Reviewed At')
                            ->disabled(),

                        Forms\Components\Textarea::make('review_reason')
                            ->label('Review Reason')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->disabled(),
                    ])
                    ->visible(fn ($record) => $record && $record->status !== 'pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requester')
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
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('requested_until')
                    ->label('Valid Until')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Permanent'),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Requester')
                    ->relationship('user', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('permission_id')
                    ->label('Permission')
                    ->relationship('permission', 'name'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('review_reason')
                            ->label('Approval Reason')
                            ->maxLength(255),
                    ])
                    ->action(function (PermissionRequest $record, array $data) {
                        $workflow = app(PermissionApprovalWorkflow::class);
                        $workflow->approve($record, auth()->user(), $data['review_reason'] ?? null);

                        \Filament\Notifications\Notification::make()
                            ->title('Request approved')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PermissionRequest $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('review_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (PermissionRequest $record, array $data) {
                        $workflow = app(PermissionApprovalWorkflow::class);
                        $workflow->reject($record, auth()->user(), $data['review_reason']);

                        \Filament\Notifications\Notification::make()
                            ->title('Request rejected')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PermissionRequest $record) => $record->status === 'pending'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PermissionRequest $record) => $record->status === 'rejected'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_all')
                        ->label('Approve All')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('review_reason')
                                ->label('Approval Reason'),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $workflow = app(PermissionApprovalWorkflow::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $workflow->approve($record, auth()->user(), $data['review_reason'] ?? null);
                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Approved {$count} requests")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('reject_all')
                        ->label('Reject All')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('review_reason')
                                ->label('Rejection Reason')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $workflow = app(PermissionApprovalWorkflow::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $workflow->reject($record, auth()->user(), $data['review_reason']);
                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Rejected {$count} requests")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionRequests::route('/'),
            'create' => Pages\CreatePermissionRequest::route('/create'),
            'edit' => Pages\EditPermissionRequest::route('/{record}/edit'),
        ];
    }
}
