
---

## 🎨 UI COMPONENTS

### PanelSwitcher Component (React/TypeScript)

**Fichier** : `resources/js/Components/PanelSwitcher.tsx`

```tsx
import { usePage } from '@inertiajs/react';
import { Fragment } from 'react';
import { Menu, Transition } from '@headlessui/react';
import {
    ChevronDownIcon,
    ShieldCheckIcon,
    BuildingStorefrontIcon,
    FireIcon,
    TruckIcon,
    CubeIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';

interface Entity {
    id: number;
    name: string;
    url: string;
    linked_shops?: string[];
    linked_kitchens?: string[];
    linked_drivers?: string[];
}

interface Panel {
    id: string;
    name: string;
    url: string;
    icon: string;
    color: string;
    entities: Entity[];
}

const iconMap: Record<string, any> = {
    'heroicon-o-shield-check': ShieldCheckIcon,
    'heroicon-o-building-storefront': BuildingStorefrontIcon,
    'heroicon-o-fire': FireIcon,
    'heroicon-o-truck': TruckIcon,
    'heroicon-o-cube': CubeIcon,
    'heroicon-o-eye': EyeIcon,
};

const colorMap: Record<string, string> = {
    danger: 'text-red-600',
    primary: 'text-blue-600',
    warning: 'text-orange-600',
    success: 'text-green-600',
    info: 'text-cyan-600',
    purple: 'text-purple-600',
};

export default function PanelSwitcher() {
    const { accessible_panels } = usePage().props as { accessible_panels: Panel[] };

    if (!accessible_panels || accessible_panels.length === 0) {
        return null;
    }

    return (
        <Menu as="div" className="relative inline-block text-left">
            <Menu.Button className="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Switch Panel
                <ChevronDownIcon className="-mr-1 h-5 w-5 text-gray-400" />
            </Menu.Button>

            <Transition
                as={Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
            >
                <Menu.Items className="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 max-h-96 overflow-y-auto focus:outline-none">
                    <div className="py-1">
                        {accessible_panels.map((panel) => {
                            const Icon = iconMap[panel.icon] || ShieldCheckIcon;
                            const colorClass = colorMap[panel.color] || 'text-gray-600';

                            return (
                                <div key={panel.id} className="border-b border-gray-100 last:border-b-0">
                                    <Menu.Item>
                                        {({ active }) => (
                                            <a
                                                href={panel.url}
                                                className={`${
                                                    active ? 'bg-gray-100' : ''
                                                } flex items-center px-4 py-3 text-sm font-medium`}
                                            >
                                                <Icon className={`mr-3 h-5 w-5 ${colorClass}`} />
                                                <span className="text-gray-900">{panel.name}</span>
                                            </a>
                                        )}
                                    </Menu.Item>

                                    {panel.entities && panel.entities.length > 0 && (
                                        <div className="pl-8 pb-2 space-y-1 bg-gray-50">
                                            {panel.entities.map((entity) => (
                                                <Menu.Item key={entity.id}>
                                                    {({ active }) => (
                                                        <div>
                                                            <a
                                                                href={entity.url}
                                                                className={`${
                                                                    active ? 'bg-gray-200' : 'bg-gray-50'
                                                                } block px-4 py-2 text-xs rounded hover:bg-gray-200 transition-colors`}
                                                            >
                                                                <span className="font-medium text-gray-900">
                                                                    {entity.name}
                                                                </span>

                                                                {/* Linked entities */}
                                                                {(entity.linked_shops?.length || 
                                                                  entity.linked_kitchens?.length || 
                                                                  entity.linked_drivers?.length) && (
                                                                    <div className="mt-1 text-xs text-gray-500 space-y-0.5">
                                                                        {entity.linked_shops && entity.linked_shops.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🏪</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_shops.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                        {entity.linked_kitchens && entity.linked_kitchens.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🔥</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_kitchens.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                        {entity.linked_drivers && entity.linked_drivers.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🚚</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_drivers.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </a>
                                                        </div>
                                                    )}
                                                </Menu.Item>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </Menu.Items>
            </Transition>
        </Menu>
    );
}
```

### Intégration dans HandleInertiaRequests

**Fichier** : `app/Http/Middleware/HandleInertiaRequests.php`

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            // Ajouter accessible_panels
            'accessible_panels' => $request->user()?->getAccessiblePanels() ?? [],
        ]);
    }
}
```

### MyPermissions Page (Tous les panels)

**Fichier** : `app/Filament/Pages/MyPermissions.php`

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MyPermissions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static string $view = 'filament.pages.my-permissions';

    protected static ?string $navigationGroup = 'My Account';

    protected static ?string $title = 'My Permissions';

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'user' => $user,
            'roles' => $user->roles()->with('permissions')->get(),
            'directPermissions' => $user->permissions,
            'inheritedPermissions' => $user->roles->flatMap->permissions->unique('id'),
            'groups' => $user->userGroups()->with('permissions')->get(),
        ];
    }
}
```

**Vue Blade** : `resources/views/filament/pages/my-permissions.blade.php`

```blade
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- User Info --}}
        <x-filament::section>
            <x-slot name="heading">
                User Information
            </x-slot>
            <x-slot name="description">
                Your account details and primary role
            </x-slot>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Name</span>
                    <p class="text-base">{{ $this->user->name }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Email</span>
                    <p class="text-base">{{ $this->user->email }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Primary Role</span>
                    <x-filament::badge color="{{ $this->user->primaryRole->color ?? 'gray' }}">
                        {{ $this->user->primaryRole->name }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        {{-- Roles --}}
        <x-filament::section>
            <x-slot name="heading">
                My Roles
            </x-slot>
            <x-slot name="description">
                Roles assigned to you with their associated scopes
            </x-slot>

            <div class="space-y-3">
                @forelse ($this->roles as $role)
                    <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <x-filament::badge color="{{ $role->color ?? 'gray' }}" class="mb-2">
                                {{ $role->name }}
                            </x-filament::badge>
                            @if ($role->pivot->scope_type)
                                <p class="text-sm text-gray-600">
                                    Scope: {{ ucfirst($role->pivot->scope_type) }}
                                    @if ($role->pivot->scope_id)
                                        (ID: {{ $role->pivot->scope_id }})
                                    @endif
                                </p>
                            @endif
                            @if ($role->pivot->valid_until)
                                <p class="text-xs text-gray-500 mt-1">
                                    Valid until: {{ $role->pivot->valid_until->format('Y-m-d H:i') }}
                                </p>
                            @endif
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $role->permissions->count() }} permissions
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No additional roles assigned</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Direct Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                Direct Permissions
            </x-slot>
            <x-slot name="description">
                Permissions assigned to you directly (not through roles)
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->directPermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="success" size="xs">
                            ✓
                        </x-filament::badge>
                        {{ $permission->name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No direct permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Inherited Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                Permissions via Roles
            </x-slot>
            <x-slot name="description">
                All permissions you have through your assigned roles
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->inheritedPermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="info" size="xs">
                            🔹
                        </x-filament::badge>
                        {{ $permission->name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No inherited permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- User Groups --}}
        @if ($this->groups->count() > 0)
            <x-filament::section>
                <x-slot name="heading">
                    My Groups
                </x-slot>
                <x-slot name="description">
                    Groups you belong to
                </x-slot>

                <div class="space-y-2">
                    @foreach ($this->groups as $group)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span class="font-medium">{{ $group->name }}</span>
                            <span class="text-xs text-gray-500">
                                {{ $group->permissions->count() }} permissions
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
```

---

## 🧪 TESTS PEST

### tests/Feature/MultiPanelUserTest.php

```php
<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Driver;
use App\Models\Supervisor;
use App\Models\Role;

test('multi-panel user can access all their panels', function () {
    $user = User::factory()->create();

    $shop = Shop::factory()->create();
    $kitchen = Kitchen::factory()->create();
    $driver = Driver::factory()->create();
    $supervisor = Supervisor::factory()->create();

    $user->shops()->attach($shop->id);
    $user->kitchens()->attach($kitchen->id);
    $user->drivers()->attach($driver->id);
    $user->supervisors()->attach($supervisor->id);

    $panels = $user->getAccessiblePanels();

    expect($panels)->toHaveCount(4);
    expect($panels[0]['id'])->toBe('shop');
    expect($panels[0]['entities'])->toHaveCount(1);
    expect($panels[1]['id'])->toBe('kitchen');
});

test('user sees managed entities in each panel', function () {
    $user = User::factory()->create();

    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $user->shops()->attach([$shop1->id, $shop2->id]);

    $managedShops = $user->getManagedShops();

    expect($managedShops)->toHaveCount(2);
    expect($user->managesShop($shop1->id))->toBeTrue();
    expect($user->managesShop($shop2->id))->toBeTrue();
});

test('template applies roles and permissions to user', function () {
    $user = User::factory()->create();
    $template = DefaultPermissionTemplate::factory()->create([
        'scope_type' => 'shop',
        'scope_id' => 1,
    ]);

    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    $template->roles()->attach($role->id);
    $template->permissions()->attach($permission->id);

    $template->applyToUser($user);

    expect($user->fresh()->roles)->toHaveCount(1);
    expect($user->fresh()->permissions)->toHaveCount(1);
});

test('cross-entity links work correctly', function () {
    $shop = Shop::factory()->create();
    $kitchen = Kitchen::factory()->create();
    $driver = Driver::factory()->create();

    $shop->kitchens()->attach($kitchen->id);
    $shop->drivers()->attach($driver->id);
    $kitchen->drivers()->attach($driver->id);

    expect($shop->kitchens)->toHaveCount(1);
    expect($shop->drivers)->toHaveCount(1);
    expect($kitchen->shops)->toHaveCount(1);
    expect($kitchen->drivers)->toHaveCount(1);
    expect($driver->shops)->toHaveCount(1);
    expect($driver->kitchens)->toHaveCount(1);
});

test('admin can see all entities', function () {
    $admin = User::factory()->create();
    $adminRole = Role::where('slug', 'super_admin')->first();
    $admin->update(['primary_role_id' => $adminRole->id]);

    Shop::factory()->count(3)->create();
    Kitchen::factory()->count(2)->create();

    expect($admin->getManagedShops())->toHaveCount(3);
    expect($admin->getManagedKitchens())->toHaveCount(2);
});

test('shop manager only sees their shops', function () {
    $manager = User::factory()->create();
    $shopManagerRole = Role::where('slug', 'shop_manager')->first();
    $manager->update(['primary_role_id' => $shopManagerRole->id]);

    $myShop = Shop::factory()->create();
    $otherShop = Shop::factory()->create();

    $manager->shops()->attach($myShop->id);

    expect($manager->getManagedShops())->toHaveCount(1);
    expect($manager->managesShop($myShop->id))->toBeTrue();
    expect($manager->managesShop($otherShop->id))->toBeFalse();
});
```

---

## ✅ CHECKLIST FINALE D'IMPLÉMENTATION

### Phase 1: Base de Données ✓
- [x] Créer 15 migrations (supervisors, kitchens, drivers, templates, configs, 10 pivots)
- [x] Vérifier foreign keys et indexes
- [x] Exécuter `php artisan migrate`

### Phase 2: Modèles ✓
- [x] Créer 5 nouveaux modèles avec relations
- [x] Étendre User avec méthodes getManaged*()
- [x] Étendre Shop/Supplier avec relations cross
- [x] Implémenter interfaces Filament (HasName)

### Phase 3: Panel Providers ✓
- [x] KitchenPanelProvider
- [x] DriverPanelProvider
- [x] SupervisorPanelProvider
- [x] Enregistrer dans bootstrap/providers.php

### Phase 4: Seeders ✓
- [x] Étendre RoleSeeder (6 nouveaux rôles)
- [x] Étendre PermissionSeeder (40+ permissions)
- [x] RolePermissionSeeder complet
- [x] DefaultPermissionTemplateSeeder
- [x] PanelConfigurationSeeder
- [x] MultiPanelUserSeeder
- [x] Mettre à jour DatabaseSeeder

### Phase 5: UI Components ✓
- [x] PanelSwitcher.tsx
- [x] Intégrer dans HandleInertiaRequests
- [x] MyPermissions page (tous panels)
- [x] Installer @headlessui/react @heroicons/react

### Phase 6: Tests ✓
- [x] Tests multi-entity user
- [x] Tests cross-links
- [x] Tests templates
- [x] Tests panel switching
- [x] Tests permissions scoped

### Phase 7: Installation & Déploiement
- [ ] `npm install @headlessui/react @heroicons/react`
- [ ] `npm run build`
- [ ] `php artisan migrate:fresh --seed`
- [ ] Test connexion moussa@noflaye.sn / password
- [ ] Vérifier panel switcher fonctionnel
- [ ] Vérifier tous les panels accessibles

---

## 🎯 CONNEXION TEST

**Email** : `moussa@noflaye.sn`  
**Password** : `password`

**Accès automatique à** :
- ✅ Admin Panel (si admin)
- ✅ 2 Shops (Dakar Centre, Plateau)
- ✅ 2 Kitchens (Centrale Dakar, Express Almadies)
- ✅ 1 Driver (Rapide Dakar)
- ✅ 1 Supervisor (Régionale Dakar)

**Panel Switcher** affichera toutes ces entités avec leurs liens croisés !

---

## 📚 COMMANDES FINALES

```bash
# Installation JS
npm install @headlessui/react @heroicons/react

# Build assets
npm run build

# Migrations & Seed
php artisan migrate:fresh --seed

# Clear caches
php artisan optimize:clear
php artisan filament:cache-components

# Démarrer serveurs
php artisan serve
npm run dev
```

---

## 🎉 RÉSULTAT FINAL

Vous aurez :
- ✅ 6 Panels Filament multi-tenant
- ✅ Architecture RBAC complète avec scopes
- ✅ Templates de permissions
- ✅ Panel Switcher fonctionnel
- ✅ Relations many-to-many flexibles
- ✅ Utilisateur test multi-panels
- ✅ UI intuitive avec liens cross-entités
- ✅ Tests complets

**🚀 Prêt pour implémentation avec Claude Code !**
