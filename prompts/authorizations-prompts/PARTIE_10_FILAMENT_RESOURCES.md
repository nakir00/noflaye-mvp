# 🚀 PROMPT CLAUDE CODE - PARTIE 10 : FILAMENT RESOURCES (FINALE)

> **Contexte** : Créer interface admin complète avec Filament v4 pour gestion permissions

---

## 📋 OBJECTIF

Créer **14 fichiers** (5 resources + 2 pages + 4 widgets + 3 modifications) pour finaliser l'UI admin.

**Principe** : Resources CRUD, Pages custom, Widgets analytics, et modifications resources existantes.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture Filament**
- ✅ Filament v4 syntax
- ✅ Forms avec sections
- ✅ Tables avec filters & actions
- ✅ Widgets avec charts
- ✅ Infolist pour readonly views

### **UX/UI**
- ✅ Navigation intuitive
- ✅ Bulk actions
- ✅ Search & filters
- ✅ Stats & analytics
- ✅ Responsive design

### **Code Quality**
- ✅ PHPDoc exhaustif
- ✅ Type hints partout
- ✅ Heroicons pour icons
- ✅ < 250 lignes par fichier

---

## 📁 LISTE DES 14 FICHIERS

### **Resources CRUD (5)**
```
app/Filament/Resources/PermissionTemplateResource.php
app/Filament/Resources/PermissionWildcardResource.php
app/Filament/Resources/PermissionDelegationResource.php
app/Filament/Resources/PermissionRequestResource.php
app/Filament/Resources/PermissionAuditLogResource.php
```

### **Pages Custom (2)**
```
app/Filament/Pages/PermissionAnalyticsDashboard.php
app/Filament/Pages/MyDelegations.php
```

### **Widgets (4)**
```
app/Filament/Widgets/PermissionStatsWidget.php
app/Filament/Widgets/PermissionGrowthChart.php
app/Filament/Widgets/MostUsedPermissionsWidget.php
app/Filament/Widgets/TemplateAdoptionWidget.php
```

### **Modifications (3)**
```
Modify: app/Filament/Resources/UserResource.php
Delete: app/Filament/Resources/RoleResource.php
Delete: app/Filament/Resources/DefaultPermissionTemplateResource.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **RESOURCE 1 : PermissionTemplateResource**

**Fichier** : `app/Filament/Resources/PermissionTemplateResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionTemplateResource\Pages;
use App\Models\PermissionTemplate;
use App\Models\Permission;
use App\Models\PermissionWildcard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermissionTemplateResource extends Resource
{
    protected static ?string $model = PermissionTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                $set('slug', \Illuminate\Support\Str::slug($state))
                            ),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Hierarchy & Scope')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Template')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('scope_id')
                            ->label('Scope')
                            ->relationship('scope', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Appearance')
                    ->schema([
                        Forms\Components\ColorPicker::make('color')
                            ->required()
                            ->default('#3B82F6'),
                        
                        Forms\Components\TextInput::make('icon')
                            ->required()
                            ->default('heroicon-o-shield-check')
                            ->placeholder('heroicon-o-shield-check')
                            ->helperText('Use Heroicons format'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ]),

                Forms\Components\Section::make('Wildcards')
                    ->schema([
                        Forms\Components\CheckboxList::make('wildcards')
                            ->relationship('wildcards', 'pattern')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ]),

                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                        
                        Forms\Components\Toggle::make('is_system')
                            ->label('System Template')
                            ->default(false)
                            ->inline(false)
                            ->helperText('System templates cannot be deleted'),
                        
                        Forms\Components\Toggle::make('auto_sync_users')
                            ->label('Auto Sync Users')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Automatically sync permissions to assigned users'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->searchable()
                    ->sortable()
                    ->placeholder('None'),
                
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('wildcards_count')
                    ->counts('wildcards')
                    ->label('Wildcards')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_system')
                    ->label('System')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Template')
                    ->relationship('parent', 'name'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('System Template'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('sync_users')
                    ->label('Sync Users')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (PermissionTemplate $record) {
                        $count = $record->syncUsers();
                        \Filament\Notifications\Notification::make()
                            ->title("Synced to {$count} users")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PermissionTemplate $record) => $record->auto_sync_users),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (PermissionTemplate $record) {
                        if ($record->is_system) {
                            throw new \Exception('Cannot delete system template');
                        }
                        if ($record->users()->count() > 0) {
                            throw new \Exception('Cannot delete template with assigned users');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionTemplates::route('/'),
            'create' => Pages\CreatePermissionTemplate::route('/create'),
            'edit' => Pages\EditPermissionTemplate::route('/{record}/edit'),
        ];
    }
}
```

---

### **RESOURCE 2 : PermissionWildcardResource**

**Fichier** : `app/Filament/Resources/PermissionWildcardResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionWildcardResource\Pages;
use App\Models\PermissionWildcard;
use App\Services\Permissions\WildcardExpander;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionWildcardResource extends Resource
{
    protected static ?string $model = PermissionWildcard::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Wildcard Information')
                    ->schema([
                        Forms\Components\TextInput::make('pattern')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('shops.* or *.read')
                            ->helperText('Use * for wildcards'),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('pattern_type')
                            ->options([
                                'full' => 'Full (*.*)',
                                'resource' => 'Resource (shops.*)',
                                'action' => 'Action (*.read)',
                                'macro' => 'Macro (custom)',
                            ])
                            ->required()
                            ->default('resource'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Appearance')
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->default('heroicon-o-sparkles'),
                        
                        Forms\Components\ColorPicker::make('color')
                            ->default('#8B5CF6'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('auto_expand')
                            ->default(true)
                            ->helperText('Automatically expand when permissions change'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pattern')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('pattern_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'full' => 'danger',
                        'resource' => 'success',
                        'action' => 'info',
                        'macro' => 'warning',
                    }),
                
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('auto_expand')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('last_expanded_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pattern_type'),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('auto_expand'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('expand')
                    ->label('Expand Now')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (PermissionWildcard $record) {
                        $expander = app(WildcardExpander::class);
                        $count = $expander->rebuildExpansions($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title("Expanded to {$count} permissions")
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('expand_all')
                        ->label('Expand All')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $expander = app(WildcardExpander::class);
                            $total = 0;
                            
                            foreach ($records as $record) {
                                $total += $expander->rebuildExpansions($record);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title("Expanded to {$total} permissions")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionWildcards::route('/'),
            'create' => Pages\CreatePermissionWildcard::route('/create'),
            'edit' => Pages\EditPermissionWildcard::route('/{record}/edit'),
        ];
    }
}
```

---

### **RESOURCES 3-5** : Structure similaire fournie

**PermissionDelegationResource.php** (~200 lignes)
**PermissionRequestResource.php** (~200 lignes)
**PermissionAuditLogResource.php** (~150 lignes)

---

### **PAGE 1 : PermissionAnalyticsDashboard**

**Fichier** : `app/Filament/Pages/PermissionAnalyticsDashboard.php`

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PermissionAnalyticsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.permission-analytics-dashboard';

    protected static ?string $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Permission Analytics';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PermissionStatsWidget::class,
            \App\Filament\Widgets\PermissionGrowthChart::class,
            \App\Filament\Widgets\MostUsedPermissionsWidget::class,
            \App\Filament\Widgets\TemplateAdoptionWidget::class,
        ];
    }
}
```

---

### **WIDGETS** : Structure fournie pour les 4 widgets

**PermissionStatsWidget.php** - StatsOverview
**PermissionGrowthChart.php** - LineChart
**MostUsedPermissionsWidget.php** - Table
**TemplateAdoptionWidget.php** - PieChart

---

## ✅ CHECKLIST VALIDATION

Pour chaque resource :

- [ ] PHPDoc complet
- [ ] Form avec sections
- [ ] Table avec filters & actions
- [ ] Bulk actions
- [ ] Navigation group & sort
- [ ] Icons Heroicons

Pour chaque widget :

- [ ] Type approprié (Stats/Chart/Table)
- [ ] Data from services
- [ ] Responsive
- [ ] Cache si approprié

---

## 🚀 COMMANDE

**Génère les 14 fichiers** :

**5 Resources** + leurs Pages :
```
app/Filament/Resources/PermissionTemplateResource.php
app/Filament/Resources/PermissionTemplateResource/Pages/...
app/Filament/Resources/PermissionWildcardResource.php
app/Filament/Resources/PermissionWildcardResource/Pages/...
app/Filament/Resources/PermissionDelegationResource.php
app/Filament/Resources/PermissionRequestResource.php
app/Filament/Resources/PermissionAuditLogResource.php
```

**2 Pages Custom** :
```
app/Filament/Pages/PermissionAnalyticsDashboard.php
app/Filament/Pages/MyDelegations.php
resources/views/filament/pages/permission-analytics-dashboard.blade.php
resources/views/filament/pages/my-delegations.blade.php
```

**4 Widgets** :
```
app/Filament/Widgets/PermissionStatsWidget.php
app/Filament/Widgets/PermissionGrowthChart.php
app/Filament/Widgets/MostUsedPermissionsWidget.php
app/Filament/Widgets/TemplateAdoptionWidget.php
```

**3 Modifications** :
```
Modify: app/Filament/Resources/UserResource.php (add tabs)
Delete: app/Filament/Resources/RoleResource.php
Delete: app/Filament/Resources/DefaultPermissionTemplateResource.php
```

---

**GO ! 🎯**
