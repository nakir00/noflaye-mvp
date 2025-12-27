# 🚀 PROMPT CLAUDE CODE - PARTIE 10 ÉTAPE 4 : MODIFICATIONS FINALES

> **Contexte** : Modifier UserResource et supprimer anciennes resources pour finaliser UI

---

## 📋 OBJECTIF

**Modifier 1 fichier** et **supprimer 2 fichiers** pour finaliser l'interface admin.

**Principe** : Ajouter tabs permissions au UserResource et nettoyer ancien système.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture Filament**
- ✅ Filament v4 syntax
- ✅ RelationManagers pour tabs
- ✅ Tables avec actions rapides
- ✅ Inline editing
- ✅ Service integration

### **Business Logic**
- ✅ Quick assign/revoke permissions
- ✅ Template assignment
- ✅ Delegation viewing
- ✅ Validations appropriées

### **Code Quality**
- ✅ PHPDoc exhaustif
- ✅ Type hints partout
- ✅ < 150 lignes par RelationManager
- ✅ Production-ready

---

## 📁 LISTE DES ACTIONS

### **Modifications (1 fichier + 3 RelationManagers)**
```
Modify: app/Filament/Resources/UserResource.php
Create: app/Filament/Resources/UserResource/RelationManagers/PermissionsRelationManager.php
Create: app/Filament/Resources/UserResource/RelationManagers/TemplatesRelationManager.php
Create: app/Filament/Resources/UserResource/RelationManagers/DelegationsRelationManager.php
```

### **Suppressions (2 fichiers)**
```
Delete: app/Filament/Resources/RoleResource.php
Delete: app/Filament/Resources/DefaultPermissionTemplateResource.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **MODIFICATION 1 : UserResource**

**Fichier** : `app/Filament/Resources/UserResource.php`

**Modification à apporter** :

```php
// Dans la méthode getRelations()
public static function getRelations(): array
{
    return [
        RelationManagers\PermissionsRelationManager::class,
        RelationManagers\TemplatesRelationManager::class,
        RelationManagers\DelegationsRelationManager::class,
    ];
}
```

---

### **CREATION 1 : PermissionsRelationManager**

**Fichier** : `app/Filament/Resources/UserResource/RelationManagers/PermissionsRelationManager.php`

```php
<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Permission;
use App\Models\Scope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * PermissionsRelationManager
 *
 * Manage user permissions
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $title = 'Direct Permissions';

    /**
     * Form for attaching permissions
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('recordId')
                    ->label('Permission')
                    ->options(Permission::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('scope_id')
                    ->label('Scope')
                    ->options(Scope::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Global'),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->nullable()
                    ->minDate(now()),

                Forms\Components\Select::make('source')
                    ->label('Source')
                    ->options([
                        'direct' => 'Direct',
                        'template' => 'Template',
                        'wildcard' => 'Wildcard',
                        'inherited' => 'Inherited',
                    ])
                    ->default('direct')
                    ->required(),

                Forms\Components\KeyValue::make('conditions')
                    ->label('Conditions')
                    ->nullable()
                    ->helperText('JSON conditions for permission'),
            ]);
    }

    /**
     * Table for managing permissions
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->badge(),

                Tables\Columns\TextColumn::make('pivot.scope.name')
                    ->label('Scope')
                    ->placeholder('Global')
                    ->badge(),

                Tables\Columns\TextColumn::make('pivot.source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'direct' => 'primary',
                        'template' => 'success',
                        'wildcard' => 'warning',
                        'inherited' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('pivot.expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->placeholder('Never'),

                Tables\Columns\IconColumn::make('pivot.conditions')
                    ->label('Has Conditions')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->pivot->conditions)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'direct' => 'Direct',
                        'template' => 'Template',
                        'wildcard' => 'Wildcard',
                        'inherited' => 'Inherited',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('scope_id')
                            ->label('Scope')
                            ->options(Scope::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->minDate(now()),
                        Forms\Components\Select::make('source')
                            ->options([
                                'direct' => 'Direct',
                                'template' => 'Template',
                                'wildcard' => 'Wildcard',
                            ])
                            ->default('direct'),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
```

---

### **CREATION 2-3** : Structure fournie

**TemplatesRelationManager.php** (~120 lignes)
- Relationship: templates
- Table: name, slug, permissions_count, auto_sync
- Actions: Attach, Detach
- Form: Template select, scope, auto_sync toggle

**DelegationsRelationManager.php** (~130 lignes)
- Relationship: delegationsReceived (custom query)
- Table: delegator, permission, scope, status, valid_until
- Actions: View only (readonly)
- Filter: status

---

## ✅ CHECKLIST VALIDATION

Pour UserResource modification :

- [ ] getRelations() updated avec 3 RelationManagers
- [ ] Imports ajoutés

Pour chaque RelationManager :

- [ ] PHPDoc complet
- [ ] Form approprié
- [ ] Table avec colonnes utiles
- [ ] Actions pertinentes
- [ ] < 150 lignes

Pour suppressions :

- [ ] Fichiers supprimés
- [ ] Références supprimées

---

## 🚀 COMMANDE

**Exécute les actions suivantes** :

### **1. Modifier UserResource**
```
app/Filament/Resources/UserResource.php
→ Update getRelations() method
→ Add imports for RelationManagers
```

### **2. Créer 3 RelationManagers**
```
app/Filament/Resources/UserResource/RelationManagers/PermissionsRelationManager.php (complet ci-dessus)
app/Filament/Resources/UserResource/RelationManagers/TemplatesRelationManager.php (structure fournie)
app/Filament/Resources/UserResource/RelationManagers/DelegationsRelationManager.php (structure fournie)
```

### **3. Supprimer 2 fichiers**
```bash
# Delete old RBAC
rm app/Filament/Resources/RoleResource.php
rm -rf app/Filament/Resources/RoleResource/

# Delete old template resource
rm app/Filament/Resources/DefaultPermissionTemplateResource.php
rm -rf app/Filament/Resources/DefaultPermissionTemplateResource/
```

**Chaque fichier doit :**
1. PHPDoc exhaustif
2. Type hints complets
3. Service integration appropriée
4. Actions validées
5. Être production-ready

---

**GO ! 🎯**

**Après ÉTAPE 4 → PROJET 100% COMPLET ! 🎉**
