# 🚀 PROMPT CLAUDE CODE - PARTIE 10 ÉTAPE 2 : 3 RESOURCES RESTANTES

> **Contexte** : Créer les 3 dernières resources (Delegation, Request, AuditLog) avec leurs pages

---

## 📋 OBJECTIF

Créer **3 resources complètes** avec leurs **9 pages** (List/Create/Edit pour chaque).

**Principe** : Resources CRUD avec actions spécialisées et validations métier.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture Filament**
- ✅ Filament v4 syntax
- ✅ Forms avec sections
- ✅ Tables avec filters & actions
- ✅ Service integration (Delegator, ApprovalWorkflow, Analytics)
- ✅ Notifications utilisateur

### **Business Logic**
- ✅ Delegation: Revoke, Extend actions
- ✅ Request: Approve, Reject workflow
- ✅ AuditLog: Readonly, Export capability
- ✅ Validations appropriées

### **Code Quality**
- ✅ PHPDoc exhaustif
- ✅ Type hints partout
- ✅ < 250 lignes par resource
- ✅ Pages standard (List/Create/Edit)

---

## 📁 LISTE DES 12 FICHIERS

### **Resources (3)**
```
app/Filament/Resources/PermissionDelegationResource.php
app/Filament/Resources/PermissionRequestResource.php
app/Filament/Resources/PermissionAuditLogResource.php
```

### **Pages (9)**
```
app/Filament/Resources/PermissionDelegationResource/Pages/ListPermissionDelegations.php
app/Filament/Resources/PermissionDelegationResource/Pages/CreatePermissionDelegation.php
app/Filament/Resources/PermissionDelegationResource/Pages/EditPermissionDelegation.php

app/Filament/Resources/PermissionRequestResource/Pages/ListPermissionRequests.php
app/Filament/Resources/PermissionRequestResource/Pages/CreatePermissionRequest.php
app/Filament/Resources/PermissionRequestResource/Pages/EditPermissionRequest.php

app/Filament/Resources/PermissionAuditLogResource/Pages/ListPermissionAuditLogs.php
(Pas de Create/Edit pour AuditLog - readonly)
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **RESOURCE 1 : PermissionDelegationResource**

**Fichier** : `app/Filament/Resources/PermissionDelegationResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionDelegationResource\Pages;
use App\Models\PermissionDelegation;
use App\Services\Permissions\PermissionDelegator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * PermissionDelegationResource
 *
 * Filament resource for managing permission delegations
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionDelegationResource extends Resource
{
    protected static ?string $model = PermissionDelegation::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';

    protected static ?string $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Delegation Information')
                    ->schema([
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

                Forms\Components\Section::make('Validity Period')
                    ->schema([
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

                Forms\Components\Section::make('Re-delegation Options')
                    ->schema([
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

                Forms\Components\Section::make('Additional Information')
                    ->schema([
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
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('revoke')
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
                        $delegator->revoke($record, auth()->user(), $data['revocation_reason']);

                        \Filament\Notifications\Notification::make()
                            ->title('Delegation revoked')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PermissionDelegation $record) => $record->isActive()),

                Tables\Actions\Action::make('extend')
                    ->label('Extend')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->form([
                        Forms\Components\DateTimePicker::make('new_expiration')
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

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PermissionDelegation $record) => !$record->isActive()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('revoke_all')
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
                                    $delegator->revoke($record, auth()->user(), $data['revocation_reason']);
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
```

---

### **RESOURCE 2-3** : Structure fournie

**PermissionRequestResource.php** (~220 lignes)
- Form: Request details (readonly après création)
- Table: Status workflow avec badges
- Actions: Approve, Reject avec forms
- Bulk Actions: Bulk Approve

**PermissionAuditLogResource.php** (~150 lignes)
- Table only (pas de form)
- Extensive filters: user, action, permission, date range
- Export action: CSV
- Pas de Create/Edit (readonly)

---

## ✅ CHECKLIST VALIDATION

Pour chaque resource :

- [ ] PHPDoc complet
- [ ] Form approprié (ou pas de form pour AuditLog)
- [ ] Table avec colonnes utiles
- [ ] Filters pertinents
- [ ] Actions métier (Revoke, Extend, Approve, Reject)
- [ ] Bulk actions
- [ ] Service integration
- [ ] Notifications
- [ ] Navigation group & sort

Pour chaque page :

- [ ] Extends correct class
- [ ] Resource reference
- [ ] Header actions si approprié

---

## 🚀 COMMANDE

**Génère les 12 fichiers** :

**3 Resources** :
```
app/Filament/Resources/PermissionDelegationResource.php (complet ci-dessus)
app/Filament/Resources/PermissionRequestResource.php (structure fournie)
app/Filament/Resources/PermissionAuditLogResource.php (structure fournie)
```

**9 Pages** (ou 7 car AuditLog n'a pas Create/Edit) :
```
PermissionDelegation: List, Create, Edit
PermissionRequest: List, Create, Edit
PermissionAuditLog: List seulement
```

**Chaque fichier doit :**
1. PHPDoc exhaustif
2. Type hints complets
3. Service integration appropriée
4. Actions métier validées
5. Notifications utilisateur
6. Être production-ready

---

**GO ! 🎯**
