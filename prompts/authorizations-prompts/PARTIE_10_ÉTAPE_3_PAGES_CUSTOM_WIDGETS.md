# 🚀 PROMPT CLAUDE CODE - PARTIE 10 ÉTAPE 3 : PAGES CUSTOM + WIDGETS

> **Contexte** : Créer pages custom analytics et widgets pour dashboards

---

## 📋 OBJECTIF

Créer **2 pages custom** + **4 widgets** + **2 blade views** pour analytics et dashboards.

**Principe** : Pages personnalisées avec widgets intégrés, charts, stats, et tables.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture Filament**
- ✅ Filament v4 syntax
- ✅ Widgets avec charts (LineChart, PieChart)
- ✅ Stats widgets avec icons
- ✅ Service integration (PermissionAnalytics)
- ✅ Responsive design

### **Data & Performance**
- ✅ Cache approprié (from services)
- ✅ Eager loading
- ✅ Real-time data
- ✅ Optimized queries

### **Code Quality**
- ✅ PHPDoc exhaustif
- ✅ Type hints partout
- ✅ < 100 lignes par widget
- ✅ Blade views avec Tailwind

---

## 📁 LISTE DES 8 FICHIERS

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

### **Blade Views (2)**
```
resources/views/filament/pages/permission-analytics-dashboard.blade.php
resources/views/filament/pages/my-delegations.blade.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **PAGE 1 : PermissionAnalyticsDashboard**

**Fichier** : `app/Filament/Pages/PermissionAnalyticsDashboard.php`

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

/**
 * PermissionAnalyticsDashboard Page
 *
 * Analytics dashboard for permission system
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionAnalyticsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.permission-analytics-dashboard';

    protected static ?string $navigationGroup = 'Permissions';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Permission Analytics';

    protected static ?string $navigationLabel = 'Analytics';

    /**
     * Get the header widgets for this page
     *
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PermissionStatsWidget::class,
        ];
    }

    /**
     * Get the widgets for this page
     *
     * @return array<class-string>
     */
    protected function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\PermissionGrowthChart::class,
            \App\Filament\Widgets\MostUsedPermissionsWidget::class,
            \App\Filament\Widgets\TemplateAdoptionWidget::class,
        ];
    }

    /**
     * Get the header widgets columns
     */
    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    /**
     * Get the widgets columns
     */
    public function getWidgetsColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
```

**Blade View** : `resources/views/filament/pages/permission-analytics-dashboard.blade.php`

```blade
<x-filament-panels::page>
    {{-- Header Widgets (Stats) --}}
    @if ($this->hasHeaderWidgets())
        <x-filament-widgets::widgets
            :columns="$this->getHeaderWidgetsColumns()"
            :widgets="$this->getHeaderWidgets()"
        />
    @endif

    {{-- Main Content Widgets (Charts + Tables) --}}
    <x-filament-widgets::widgets
        :columns="$this->getWidgetsColumns()"
        :widgets="$this->getWidgets()"
    />
</x-filament-panels::page>
```

---

### **PAGE 2 : MyDelegations**

**Fichier** : `app/Filament/Pages/MyDelegations.php`

```php
<?php

namespace App\Filament\Pages;

use App\Models\PermissionDelegation;
use App\Services\Permissions\PermissionDelegator;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/**
 * MyDelegations Page
 *
 * User's delegations (received and given)
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class MyDelegations extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';

    protected static string $view = 'filament.pages.my-delegations';

    protected static ?string $navigationGroup = 'Permissions';

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
                    ->where('delegatee_id', auth()->id())
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
            ->actions([
                Tables\Actions\ViewAction::make()
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
        return PermissionDelegation::where('delegator_id', auth()->id())
            ->with(['delegatee', 'permission', 'scope'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

**Blade View** : `resources/views/filament/pages/my-delegations.blade.php`

```blade
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Received Delegations --}}
        <div>
            <h2 class="text-lg font-semibold mb-4">Delegations I Received</h2>
            {{ $this->table }}
        </div>

        {{-- Given Delegations --}}
        <div>
            <h2 class="text-lg font-semibold mb-4">Delegations I Gave</h2>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delegated To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permission</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scope</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Until</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->getGivenDelegations() as $delegation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $delegation->delegatee->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $delegation->permission->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $delegation->scope?->name ?? 'Global' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($delegation->revoked_at)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Revoked</span>
                                    @elseif ($delegation->valid_until < now())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Expired</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $delegation->valid_until->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No delegations given</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
```

---

### **WIDGETS 1-4** : Structure fournie

**PermissionStatsWidget.php** - StatsOverview avec 4 stats
**PermissionGrowthChart.php** - LineChart permissions over time
**MostUsedPermissionsWidget.php** - TableWidget top 10
**TemplateAdoptionWidget.php** - PieChart distribution

---

## ✅ CHECKLIST VALIDATION

Pour chaque page :

- [ ] PHPDoc complet
- [ ] Navigation configurée
- [ ] View blade créée
- [ ] Widgets intégrés
- [ ] Responsive layout

Pour chaque widget :

- [ ] Type approprié (Stats/Chart/Table)
- [ ] Service integration
- [ ] Cache si approprié
- [ ] PHPDoc complet
- [ ] < 100 lignes

---

## 🚀 COMMANDE

**Génère les 8 fichiers** :

**2 Pages Custom** :
```
app/Filament/Pages/PermissionAnalyticsDashboard.php (complet ci-dessus)
app/Filament/Pages/MyDelegations.php (complet ci-dessus)
```

**4 Widgets** :
```
app/Filament/Widgets/PermissionStatsWidget.php (structure fournie)
app/Filament/Widgets/PermissionGrowthChart.php (structure fournie)
app/Filament/Widgets/MostUsedPermissionsWidget.php (structure fournie)
app/Filament/Widgets/TemplateAdoptionWidget.php (structure fournie)
```

**2 Blade Views** :
```
resources/views/filament/pages/permission-analytics-dashboard.blade.php (complet ci-dessus)
resources/views/filament/pages/my-delegations.blade.php (complet ci-dessus)
```

**Chaque fichier doit :**
1. PHPDoc exhaustif
2. Type hints complets
3. Service integration appropriée
4. Responsive design
5. Être production-ready

---

**GO ! 🎯**
