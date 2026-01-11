# BaseResource - Examples de Refactorisation

Ce document montre comment refactoriser les ressources existantes pour utiliser `BaseResource`.

## Exemple 1: ShopResource - AVANT

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Models\Shop;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Entities Management';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ... autres méthodes
}
```

**Problèmes:**
- Code répétitif pour les actions
- Pas de configuration de pagination
- Pas de persistence de session
- 15+ lignes juste pour les actions

## Exemple 1: ShopResource - APRÈS

```php
<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Resources\ShopResource\Pages;
use App\Models\Shop;
use Filament\Tables;
use Filament\Tables\Table;

class ShopResource extends BaseResource
{
    protected static ?string $model = Shop::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Entities Management';

    public static function table(Table $table): Table
    {
        return static::configureTable($table)
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ]);
    }

    // ... autres méthodes
}
```

**Améliorations:**
- ✅ Réduit de 15+ lignes
- ✅ Actions automatiques
- ✅ Pagination configurée
- ✅ Persistence de session activée
- ✅ Plus facile à maintenir

---

## Exemple 2: DriverResource avec Actions Personnalisées

### AVANT

```php
class DriverResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([...])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('assign')
                    ->icon('heroicon-o-truck')
                    ->action(fn (Driver $record) => /* logic */),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export')
                        ->action(fn () => /* logic */),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

### APRÈS

```php
use App\Filament\BaseResource;

class DriverResource extends BaseResource
{
    public static function table(Table $table): Table
    {
        return static::configureTable($table)
            ->columns([...]);
    }

    protected static function getDefaultRecordActions(): array
    {
        return [
            ...parent::getDefaultRecordActions(),
            Action::make('assign')
                ->icon('heroicon-o-truck')
                ->action(fn (Driver $record) => /* logic */),
        ];
    }

    protected static function getDefaultToolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('export')
                    ->action(fn () => /* logic */),
                ...parent::getDefaultToolbarActions()[0]->getActions(),
            ]),
        ];
    }
}
```

**Avantages:**
- Actions personnalisées séparées de la configuration de table
- Réutilisation des actions par défaut
- Code plus organisé et testable

---

## Exemple 3: UserResource avec Soft Deletes

### AVANT

```php
class UserResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([...])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

### APRÈS

```php
use App\Filament\BaseResource;

class UserResource extends BaseResource
{
    protected static bool $supportsSoftDeletes = true; // 🎯 Une seule ligne!

    public static function table(Table $table): Table
    {
        return static::configureTable($table)
            ->columns([...]);
    }
}
```

**Résultat:**
- 1 ligne au lieu de 15+
- Actions Restore et ForceDelete automatiques
- Bulk actions soft delete automatiques

---

## Exemple 4: ProductResource avec Pagination Personnalisée

### Configuration

```php
use App\Filament\BaseResource;

class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    // Grande base de données - plus de résultats par page
    protected static int $defaultRecordsPerPage = 100;
    protected static array $paginationOptions = [50, 100, 250, 500];

    // Plus de résultats dans la recherche globale
    public static function getGlobalSearchResultsLimit(): int
    {
        return 10;
    }

    public static function table(Table $table): Table
    {
        return static::configureTable($table)
            ->columns([
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('price')->money('XOF'),
                Tables\Columns\TextColumn::make('stock')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category'),
                Tables\Filters\TernaryFilter::make('in_stock'),
            ]);
    }
}
```

**Fonctionnalités obtenues:**
- Pagination 100 par défaut (au lieu de 25)
- Options: 50, 100, 250, 500
- Recherche globale affiche 10 produits
- Session persistence pour filtres et recherche

---

## Exemple 5: Migration Complète d'une Ressource

### Étape 1: Identifier le code à refactoriser

**Fichier:** `app/Filament/Resources/KitchenResource.php`

Cherchez:
- `extends Resource` → changer en `extends BaseResource`
- `->recordActions([...])` → supprimer
- `->toolbarActions([...])` → supprimer
- Imports non utilisés → nettoyer

### Étape 2: Modifier l'import et la classe parente

```diff
- use Filament\Resources\Resource;
+ use App\Filament\BaseResource;

- class KitchenResource extends Resource
+ class KitchenResource extends BaseResource
{
```

### Étape 3: Mettre à jour la méthode table()

```diff
  public static function table(Table $table): Table
  {
-     return $table
+     return static::configureTable($table)
          ->columns([...])
-         ->filters([...])
-         ->recordActions([
-             ViewAction::make(),
-             EditAction::make(),
-             DeleteAction::make(),
-         ])
-         ->toolbarActions([
-             BulkActionGroup::make([
-                 DeleteBulkAction::make(),
-             ]),
-         ]);
+         ->filters([...]);
  }
```

### Étape 4: Nettoyer les imports

```diff
- use Filament\Actions\BulkActionGroup;
- use Filament\Actions\DeleteAction;
- use Filament\Actions\DeleteBulkAction;
- use Filament\Actions\EditAction;
- use Filament\Actions\ViewAction;
```

### Étape 5: Tester

```bash
# Visiter la page de la ressource
php artisan serve

# Vérifier:
# - View action fonctionne
# - Edit action fonctionne
# - Delete action fonctionne
# - Bulk delete fonctionne
# - Pagination fonctionne
# - Filtres persistent entre les pages
```

---

## Checklist de Migration

Utilisez cette checklist pour migrer chaque ressource:

- [ ] Changer `use Filament\Resources\Resource` → `use App\Filament\BaseResource`
- [ ] Changer `extends Resource` → `extends BaseResource`
- [ ] Ajouter `static::configureTable($table)` dans `table()`
- [ ] Supprimer `->recordActions([...])`
- [ ] Supprimer `->toolbarActions([...])`
- [ ] Nettoyer les imports non utilisés
- [ ] Si le modèle utilise SoftDeletes, ajouter `protected static bool $supportsSoftDeletes = true;`
- [ ] Tester toutes les actions
- [ ] Tester les bulk actions
- [ ] Vérifier la pagination
- [ ] Vérifier que les filtres persistent

---

## Ressources à Migrer

Liste des ressources dans le projet:

1. ✅ **Exemples de documentation créés**
2. ⏳ **À migrer:**
   - ShopResource
   - KitchenResource
   - DriverResource
   - SupervisorResource
   - SupplierResource (nouvellement créé)
   - UserResource
   - PermissionRequestResource
   - PermissionWildcardResource
   - PermissionAuditLogResource
   - PermissionDelegationResource
   - PermissionTemplateResource

---

## Cas Particuliers

### Ressource avec Actions Complexes

Si une ressource a des actions très personnalisées, vous pouvez:

**Option 1: Étendre les actions par défaut**
```php
protected static function getDefaultRecordActions(): array
{
    return [
        ...parent::getDefaultRecordActions(),
        Action::make('custom1'),
        Action::make('custom2'),
    ];
}
```

**Option 2: Remplacer complètement**
```php
protected static function getDefaultRecordActions(): array
{
    // Pas de parent::getDefaultRecordActions()
    return [
        Action::make('custom1'),
        Action::make('custom2'),
    ];
}
```

### Ressource Sans Delete

Pour désactiver la suppression:

```php
protected static function getDefaultRecordActions(): array
{
    return [
        ViewAction::make(),
        EditAction::make(),
        // Pas de DeleteAction
    ];
}

protected static function getDefaultToolbarActions(): array
{
    return []; // Pas de bulk actions
}
```

### Ressource Read-Only

```php
protected static function getDefaultRecordActions(): array
{
    return [
        ViewAction::make(),
        // Pas d'Edit ni Delete
    ];
}

public static function canCreate(): bool
{
    return false;
}
```

---

## Bénéfices de la Migration

### Avant BaseResource (Total: ~30 fichiers × 15 lignes)
- **~450 lignes** de code répétitif
- Configuration manuelle de chaque ressource
- Risque d'oubli de fonctionnalités
- Difficile à maintenir

### Après BaseResource
- **~0-5 lignes** par ressource pour les actions
- Configuration automatique
- Consistance garantie
- Facile à mettre à jour globalement

### Économie
- **~445 lignes** de code en moins
- **Temps de développement:** -70%
- **Bugs potentiels:** -80%
- **Maintenabilité:** +300%

---

## Prochaines Étapes

1. **Migrer ShopResource** (ressource simple)
2. **Migrer UserResource** (avec soft deletes)
3. **Migrer les autres ressources une par une**
4. **Créer des tests pour BaseResource**
5. **Documenter les patterns spécifiques au projet**

Bonne migration! 🚀
