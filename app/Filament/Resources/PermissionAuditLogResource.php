<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionAuditLogResource\Pages;
use App\Models\PermissionAuditLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * PermissionAuditLogResource
 *
 * Filament resource for viewing permission audit logs (readonly)
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionAuditLogResource extends Resource
{
    protected static ?string $model = PermissionAuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'granted' => 'success',
                        'revoked' => 'danger',
                        'updated' => 'info',
                        'expired' => 'warning',
                        'delegated' => 'primary',
                        'delegation_revoked' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permission_slug')
                    ->label('Permission')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'direct' => 'info',
                        'template' => 'success',
                        'delegation' => 'primary',
                        'wildcard' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('source_name')
                    ->label('Source Name')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('performed_by_name')
                    ->label('Performed By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'granted' => 'Granted',
                        'revoked' => 'Revoked',
                        'updated' => 'Updated',
                        'expired' => 'Expired',
                        'delegated' => 'Delegated',
                        'delegation_revoked' => 'Delegation Revoked',
                    ]),

                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'direct' => 'Direct',
                        'template' => 'Template',
                        'delegation' => 'Delegation',
                        'wildcard' => 'Wildcard',
                    ]),

                Filter::make('user_name')
                    ->schema([
                        TextInput::make('user_name')
                            ->label('User Name'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['user_name'], fn ($query, $name) =>
                            $query->where('user_name', 'like', "%{$name}%")
                        );
                    }),

                Filter::make('permission_slug')
                    ->schema([
                        TextInput::make('permission_slug')
                            ->label('Permission Slug'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['permission_slug'], fn ($query, $slug) =>
                            $query->where('permission_slug', 'like', "%{$slug}%")
                        );
                    }),

                Filter::make('performed_by_name')
                    ->schema([
                        TextInput::make('performed_by_name')
                            ->label('Performed By'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['performed_by_name'], fn ($query, $name) =>
                            $query->where('performed_by_name', 'like', "%{$name}%")
                        );
                    }),

                Filter::make('date_range')
                    ->schema([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($query, $date) =>
                                $query->whereDate('created_at', '>=', $date)
                            )
                            ->when($data['created_until'], fn ($query, $date) =>
                                $query->whereDate('created_at', '<=', $date)
                            );
                    }),

                Filter::make('ip_address')
                    ->schema([
                        TextInput::make('ip_address')
                            ->label('IP Address'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['ip_address'], fn ($query, $ip) =>
                            $query->where('ip_address', 'like', "%{$ip}%")
                        );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Audit Log Details')
                    ->modalContent(function (PermissionAuditLog $record) {
                        return view('filament.resources.permission-audit-log.view-audit-log', [
                            'record' => $record,
                        ]);
                    }),

                Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (PermissionAuditLog $record) {
                        $csv = "Action,User,User Email,Permission,Source,Performed By,IP Address,Date,Reason\n";
                        $csv .= implode(',', [
                            $record->action,
                            $record->user_name,
                            $record->user_email,
                            $record->permission_slug,
                            $record->source,
                            $record->performed_by_name,
                            $record->ip_address ?? 'N/A',
                            $record->created_at->format('Y-m-d H:i:s'),
                            str_replace(',', ';', $record->reason ?? ''),
                        ]);

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv;
                        }, 'audit-log-' . $record->id . '.csv');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export_all')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $csv = "Action,User,User Email,Permission,Source,Performed By,IP Address,Date,Reason\n";

                            foreach ($records as $record) {
                                $csv .= implode(',', [
                                    $record->action,
                                    $record->user_name,
                                    $record->user_email,
                                    $record->permission_slug,
                                    $record->source,
                                    $record->performed_by_name,
                                    $record->ip_address ?? 'N/A',
                                    $record->created_at->format('Y-m-d H:i:s'),
                                    str_replace(',', ';', $record->reason ?? ''),
                                ]) . "\n";
                            }

                            return response()->streamDownload(function () use ($csv) {
                                echo $csv;
                            }, 'audit-logs-export-' . now()->format('Y-m-d-His') . '.csv');
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionAuditLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
