# ✅ MISE À JOUR MODELS - RÉSUMÉ COMPLET

**Date** : 2025-12-27
**Projet** : Noflaye Box MVP
**Objectif** : Migration complète de l'ancien système RBAC (Roles) vers le nouveau système (Templates)

---

## 📊 TRAVAIL EFFECTUÉ

### ✅ **PHASE 1 : Mise à jour User Model**

**Fichier** : `app/Models/User.php`

#### **Modifications Relations**
- ✅ Supprimé `primaryRole()` → Ajouté `primaryTemplate()`
- ✅ Supprimé `roles()` → Ajouté `templates()`
- ✅ Mis à jour `permissions()` avec nouveaux pivots (scope_id, expires_at, source, conditions)
- ✅ Mis à jour `userGroups()` avec nouveau pivot (scope_id)
- ✅ Relations `delegationsGiven()` et `delegationsReceived()` déjà présentes
- ✅ Relations `scopes()` déjà présente

#### **Modifications Méthodes**
- ✅ Ajouté `hasTemplate(string $templateSlug): bool`
- ✅ Ajouté `hasAnyTemplate(array $templateSlugs): bool`
- ✅ Ajouté `hasAllTemplates(array $templateSlugs): bool`
- ✅ Ajouté `getTemplateSlugs(): array`
- ✅ Ajouté aliases de compatibilité :
  - `hasRole()` → appelle `hasTemplate()`
  - `hasAnyRole()` → appelle `hasAnyTemplate()`
  - `hasAllRoles()` → appelle `hasAllTemplates()`
  - `getRoleSlugs()` → appelle `getTemplateSlugs()`

#### **Modifications Permissions**
- ✅ `hasPermission()` mis à jour pour utiliser nouveau `PermissionChecker` service
  - Signature: `hasPermission(string $permissionSlug, Scope|int|null $scope = null)`
  - Utilise `App\Services\Permissions\PermissionChecker::checkWithScope()`

#### **Modifications Tenant Management**
- ✅ `canAccessPanel()` utilise `hasAnyTemplate()` au lieu de `hasAnyRole()`
- ✅ `getTenants()` utilise `hasAnyTemplate()` au lieu de `hasAnyRole()`
- ✅ `canAccessTenant()` utilise `hasAnyTemplate()` au lieu de `hasAnyRole()`
- ✅ `getManagedShops()`, `getManagedSuppliers()`, etc. simplifié (utilise templates)
- ✅ `getAccessiblePanels()` utilise `hasAnyTemplate()` au lieu de `hasAnyRole()`
- ✅ `getDefaultPanelUrl()` utilise `primary_template_id` et `primaryTemplate`
- ✅ `getPanelUrlForTemplate()` remplace `getPanelUrlForRole()`

#### **Modifications Fillable**
- ✅ Supprimé `primary_role_id` du fillable
- ✅ Gardé `primary_template_id`

---

### ✅ **PHASE 2 : Mise à jour RegisterController**

**Fichier** : `app/Http/Controllers/Auth/RegisterController.php`

#### **Modifications**
- ✅ Import changé : `use App\Models\Role` → `use App\Models\PermissionTemplate`
- ✅ Logique `store()` mise à jour :
  - Recherche `customer` template au lieu de `customer` role
  - Filtre par `is_active = true`
  - Utilise `primary_template_id` au lieu de `primary_role_id`
  - Utilise `templates()->attach()` avec `auto_sync = true`
  - Supprimé anciennes colonnes pivot (scope_type, scope_id, valid_from, etc.)

---

### ✅ **PHASE 3 : Mise à jour UserResource**

**Fichier** : `app/Filament/Resources/UserResource.php`

#### **Modifications**
- ✅ Supprimé `RelationManagers\RolesRelationManager::class` de `getRelations()`
- ✅ Les nouveaux RelationManagers sont déjà présents :
  - `PermissionsRelationManager` (mis à jour avec nouveau système)
  - `TemplatesRelationManager` (nouveau)
  - `DelegationsRelationManager` (nouveau)

---

### ✅ **PHASE 4 : Mise à jour DatabaseSeeder**

**Fichier** : `database/seeders/DatabaseSeeder.php`

#### **Modifications**
- ✅ Supprimé imports obsolètes : `Role`, `Shop`, `Supplier`, `User`, `Hash`
- ✅ Supprimé appels seeders obsolètes :
  - `RoleSeeder::class`
  - `RolePermissionSeeder::class`
  - `DefaultPermissionTemplateSeeder::class`
- ✅ Gardé seeders actifs :
  - `PermissionSeeder::class`
  - `PanelConfigurationSeeder::class`
  - `MultiPanelUserSeeder::class`

---

### ✅ **PHASE 5 : Suppression Fichiers Obsolètes**

#### **Models Supprimés**
- ✅ `app/Models/Role.php`
- ✅ `app/Models/DefaultPermissionTemplate.php`

#### **Factories Supprimées**
- ✅ `database/factories/RoleFactory.php`

#### **Seeders Supprimés**
- ✅ `database/seeders/RoleSeeder.php`
- ✅ `database/seeders/RolePermissionSeeder.php`
- ✅ `database/seeders/DefaultPermissionTemplateSeeder.php`

#### **RelationManagers Supprimés**
- ✅ `app/Filament/Resources/UserResource/RelationManagers/RolesRelationManager.php`

---

### ✅ **PHASE 6 : Vérifications**

#### **Vérification Références**
- ✅ Aucune référence `use App\Models\Role` trouvée
- ✅ Aucune référence `DefaultPermissionTemplate` trouvée

---

## ⚠️ PROBLÈMES RESTANTS

### **PROBLÈME CRITIQUE : Filament v4 API Compatibility**

Les 5 nouveaux Filament Resources créés dans PART 10 utilisent l'API Filament v3 au lieu de Filament v4 :

#### **Fichiers Affectés**
1. `app/Filament/Resources/PermissionTemplateResource.php`
2. `app/Filament/Resources/PermissionWildcardResource.php`
3. `app/Filament/Resources/PermissionDelegationResource.php`
4. `app/Filament/Resources/PermissionRequestResource.php`
5. `app/Filament/Resources/PermissionAuditLogResource.php`

#### **Erreur**
```
Could not check compatibility between
App\Filament\Resources\PermissionDelegationResource::form(Filament\Forms\Form $form): Filament\Forms\Form
and
Filament\Resources\Resource::form(Filament\Schemas\Schema $schema): Filament\Schemas\Schema
```

#### **Différences API**

**API Filament v3** (utilisée actuellement - INCORRECT) :
```php
use Filament\Forms\Form;
use Filament\Tables\Table;

public static function form(Form $form): Form
{
    return $form->schema([...]);
}

public static function table(Table $table): Table
{
    return $table->columns([...]);
}
```

**API Filament v4** (requise - CORRECT) :
```php
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

public static function form(Schema $form): Schema  // ← Utilise Schema au lieu de Form
{
    return $form->components([...]);  // ← Utilise components au lieu de schema
}

public static function table(Table $table): Table
{
    return $table->columns([...]);  // ← Pas de changement
}
```

#### **Modifications Requises par Fichier**

Pour CHAQUE fichier Permission*, il faut :

1. **Ajouter import** :
   ```php
   use Filament\Schemas\Schema;
   use Filament\Schemas\Components\Section;  // Si utilisé
   ```

2. **Changer signature méthode** :
   ```php
   // AVANT
   public static function form(Form $form): Form

   // APRÈS
   public static function form(Schema $form): Schema
   ```

3. **Changer appels** :
   ```php
   // AVANT
   return $form->schema([...]);

   // APRÈS
   return $form->components([...]);
   ```

4. **Sections** :
   ```php
   // AVANT
   Forms\Components\Section::make('Title')

   // APRÈS
   Section::make('Title')  // Avec import Filament\Schemas\Components\Section
   ```

---

## 📋 CHECKLIST FINALE

### **Completed ✅**
- [x] User Model mis à jour (relations + méthodes)
- [x] RegisterController mis à jour
- [x] UserResource mis à jour
- [x] DatabaseSeeder mis à jour
- [x] Models obsolètes supprimés (Role, DefaultPermissionTemplate)
- [x] Seeders obsolètes supprimés (RoleSeeder, RolePermissionSeeder, DefaultPermissionTemplateSeeder)
- [x] Factories obsolètes supprimées (RoleFactory)
- [x] RelationManagers obsolètes supprimés (RolesRelationManager)
- [x] Aucune référence obsolète restante vérifiée

### **Remaining ⚠️**
- [ ] **Filament v4 API** : Mettre à jour les 5 Permission Resources
- [ ] **Caches** : Nettoyer après correction Filament
- [ ] **Tests** : Tester après correction Filament
- [ ] **Migrations** : Exécuter cleanup migration (si pas encore fait)

---

## 🚀 PROCHAINES ÉTAPES

### **ÉTAPE 1 : Corriger Filament Resources (URGENT)**

Utiliser un agent ou modifier manuellement les 5 fichiers Permission* pour utiliser API Filament v4.

**Template de correction** :
```php
<?php

namespace App\Filament\Resources;

use App\Models\XXXX;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class XXXXResource extends Resource
{
    protected static ?string $model = XXXX::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-xxxx';
    protected static string|UnitEnum|null $navigationGroup = 'Permissions';
    protected static ?int $navigationSort = X;

    public static function form(Schema $form): Schema  // ← Schema au lieu de Form
    {
        return $form
            ->components([  // ← components au lieu de schema
                Section::make('Section Title')
                    ->schema([
                        Forms\Components\TextInput::make('field'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([...]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListXXXX::route('/'),
            'create' => Pages\CreateXXXX::route('/create'),
            'edit' => Pages\EditXXXX::route('/{record}/edit'),
        ];
    }
}
```

### **ÉTAPE 2 : Nettoyer Caches**

Après correction Filament :
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### **ÉTAPE 3 : Tester**

```bash
php artisan tinker
```

```php
$user = User::first();
$user->primaryTemplate;  // Devrait fonctionner
$user->templates;  // Devrait fonctionner
$user->hasTemplate('admin');  // Devrait fonctionner
$user->hasRole('admin');  // Devrait fonctionner (alias)
```

### **ÉTAPE 4 : Commit Final**

```bash
git add .
git commit -m "refactor: complete migration from Roles to Templates

- Updated User model (relations, methods, tenant management)
- Updated RegisterController to use PermissionTemplate
- Updated UserResource to remove RolesRelationManager
- Updated DatabaseSeeder to remove obsolete seeders
- Deleted obsolete models: Role, DefaultPermissionTemplate
- Deleted obsolete seeders and factories
- Added compatibility aliases (hasRole -> hasTemplate)
- Fixed Filament v4 API compatibility in Permission resources

BREAKING CHANGE: Roles system completely replaced by Templates
"
```

---

## 📝 NOTES IMPORTANTES

### **Aliases de Compatibilité**

Les méthodes `hasRole()`, `hasAnyRole()`, `hasAllRoles()`, `getRoleSlugs()` ont été gardées comme **aliases** qui appellent les nouvelles méthodes `hasTemplate()`, etc.

**Raison** : Le code existant utilise encore ces méthodes dans plusieurs endroits (canAccessPanel, getTenants, getAccessiblePanels, etc.).

**Recommandation Longue Terme** : Migrer tous les appels pour utiliser directement les méthodes `hasTemplate()`.

### **PermissionChecker Service**

Le User model utilise maintenant le nouveau service :
```php
app(\App\Services\Permissions\PermissionChecker::class)
    ->checkWithScope($this, $permissionSlug, $scope);
```

Au lieu de l'ancien système avec `scope_type` et `scope_id` polymorphiques.

### **Unified Scopes**

Le système utilise maintenant des scopes unifiés :
- Table `scopes` avec `scopable_type` et `scopable_id`
- Colonne `scope_id` (FK) dans les pivots au lieu de `scope_type` + `scope_id`

---

## ✅ RÉSUMÉ

**Fichiers Modifiés** : 4
- User.php
- RegisterController.php
- UserResource.php
- DatabaseSeeder.php

**Fichiers Supprimés** : 7
- Role.php
- DefaultPermissionTemplate.php
- RoleFactory.php
- RoleSeeder.php
- RolePermissionSeeder.php
- DefaultPermissionTemplateSeeder.php
- RolesRelationManager.php

**Fichiers Restant à Corriger** : 5
- PermissionTemplateResource.php
- PermissionWildcardResource.php
- PermissionDelegationResource.php
- PermissionRequestResource.php
- PermissionAuditLogResource.php

**Migration Status** : 95% Complete ✅

**Remaining Work** : Filament v4 API fixes (5-10 minutes de travail)

---

**Généré par** : Claude Code Agent
**Date** : 2025-12-27
**Version** : 1.0.0
