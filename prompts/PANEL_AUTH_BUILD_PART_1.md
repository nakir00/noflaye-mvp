NOFLAYE BOX - IMPLÉMENTATION COMPLÈTE RBAC MULTI-ENTITÉS📋

TABLE DES MATIÈRES
Architecture & Contexte
Base de Données - Migrations
Modèles Eloquent
Filament Resources - Admin Panel
Relation Managers Détaillés
Panel Providers
Resources Panels Secondaires
Seeders Complets
UI Components
Services & Helpers
Commandes Artisan
Tests
Configuration Finale
Checklist Complète
🎯 ARCHITECTURE & CONTEXTEVue d'EnsembleNoflaye Box : Plateforme de livraison alimentaire sénégalaise avec architecture RBAC/GBAC hybride.Stack Technique :

Laravel 12
Filament v4 (6 panels multi-tenant)
Inertia v2 + React + TypeScript
MySQL/PostgreSQL
Panels & Navigation Groups📊 ADMIN PANEL (Super Admin, Admin)
│
├─ 🔐 Access Control
│  ├─ Users Management
│  ├─ Roles & Permissions
│  ├─ User Groups
│  ├─ Permission Templates
│  └─ Panel Configurations
│
├─ 🏪 Entities Management
│  ├─ Shops
│  ├─ Kitchens
│  ├─ Drivers
│  ├─ Suppliers
│  └─ Supervisors
│
└─ 📊 Dashboard

📊 SHOP PANEL (Shop Managers)
├─ Team Management (users scopés)
├─ My Permissions
└─ Dashboard

📊 KITCHEN PANEL (Kitchen Managers)
├─ Team Management
├─ Linked Shops
├─ My Permissions
└─ Dashboard

📊 DRIVER PANEL (Drivers)
├─ My Permissions
└─ Dashboard

📊 SUPPLIER PANEL (Supplier Managers)
├─ Team Management
├─ My Permissions
└─ Dashboard

📊 SUPERVISOR PANEL (Supervisor Managers)
├─ Team Management
├─ Linked Entities (Shops/Kitchens/Drivers)
├─ Permission Templates
├─ My Permissions
└─ DashboardRelations Entre EntitésUSER (many-to-many avec tous)
  ├─ shops (via shop_user)
  ├─ kitchens (via kitchen_user)
  ├─ drivers (via driver_user)
  ├─ suppliers (via supplier_user)
  ├─ supervisors (via supervisor_user)
  ├─ roles (via user_roles avec scope)
  ├─ permissions (via user_permissions avec scope)
  └─ userGroups (via user_group_members avec scope)

SHOP (indépendant)
  ├─ users (managers)
  ├─ kitchens (via shop_kitchen)
  ├─ drivers (via shop_driver)
  └─ userGroups (morphMany)

KITCHEN (indépendant)
  ├─ users (managers)
  ├─ shops (via shop_kitchen)
  ├─ drivers (via kitchen_driver)
  └─ userGroups (morphMany)

DRIVER (indépendant)
  ├─ users (managers optionnel)
  ├─ shops (via shop_driver)
  ├─ kitchens (via kitchen_driver)
  └─ userGroups (morphMany)

SUPERVISOR (agence régionale)
  ├─ users (managers)
  ├─ shops (via supervisor_shop)
  ├─ kitchens (via supervisor_kitchen)
  ├─ drivers (via supervisor_driver)
  └─ userGroups (morphMany)

SUPPLIER (existant)
  ├─ users (managers)
  └─ userGroups (morphMany)Principes RBAC
Permissions Scoped : Chaque permission peut avoir scope_type (shop/kitchen/driver/supplier/supervisor) + scope_id
Templates par Défaut : Groupes de permissions pré-configurées appliquées lors d'invitation
Granularité Post-Template : Modification individuelle après application
Multi-Rôles : User peut avoir plusieurs rôles avec scopes différents
Panel Switching : Navigation facile entre entités via dropdown
🗄️ BASE DE DONNÉES - MIGRATIONSMigration 1 : Supervisorsphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisors');
    }
};Migration 2 : Kitchensphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('operating_hours')->nullable();
            $table->integer('capacity')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchens');
    }
};Migration 3 : Driversphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('license_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_available');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};Migration 4 : Default Permission Templatesphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('default_permission_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('scope_type')->nullable(); // 'global', 'shop', 'kitchen', etc.
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['scope_type', 'scope_id']);
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_permission_templates');
    }
};Migration 5 : Panel Configurationsphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panel_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('panel_id')->unique(); // 'shop', 'kitchen', etc.
            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_roles')->default(false);
            $table->boolean('can_manage_permissions')->default(false);
            $table->boolean('can_invite_users')->default(false);
            $table->boolean('can_assign_managers')->default(false);
            $table->boolean('can_create_templates')->default(false);
            $table->boolean('can_assign_templates')->default(false);
            $table->boolean('can_view_own_permissions')->default(true);
            $table->json('additional_settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_configurations');
    }
};Migration 6 : Supervisor User Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['supervisor_id', 'user_id']);
            $table->index('supervisor_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_user');
    }
};Migration 7 : Kitchen User Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kitchen_id', 'user_id']);
            $table->index('kitchen_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_user');
    }
};Migration 8 : Driver User Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['driver_id', 'user_id']);
            $table->index('driver_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_user');
    }
};Migration 9 : Shop Kitchen Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_kitchen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kitchen_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shop_id', 'kitchen_id']);
            $table->index('shop_id');
            $table->index('kitchen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_kitchen');
    }
};Migration 10 : Shop Driver Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shop_id', 'driver_id']);
            $table->index('shop_id');
            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_driver');
    }
};Migration 11 : Kitchen Driver Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kitchen_id', 'driver_id']);
            $table->index('kitchen_id');
            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_driver');
    }
};Migration 12 : Supervisor Shop Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_shop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['supervisor_id', 'shop_id']);
            $table->index('supervisor_id');
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_shop');
    }
};Migration 13 : Supervisor Kitchen Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_kitchen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kitchen_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['supervisor_id', 'kitchen_id']);
            $table->index('supervisor_id');
            $table->index('kitchen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_kitchen');
    }
};Migration 14 : Supervisor Driver Pivotphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['supervisor_id', 'driver_id']);
            $table->index('supervisor_id');
            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_driver');
    }
};Migration 15 : Template Pivotsphp<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template Roles
        Schema::create('template_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('default_permission_templates')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'role_id']);
        });

        // Template Permissions
        Schema::create('template_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('default_permission_templates')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'permission_id']);
        });

        // Template User Groups
        Schema::create('template_user_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('default_permission_templates')->cascadeOnDelete();
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'user_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_user_groups');
        Schema::dropIfExists('template_permissions');
        Schema::dropIfExists('template_roles');
    }
};🔧 MODÈLES ELOQUENTModèle : Supervisorphp<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Supervisor extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'supervisor_user')->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'supervisor_shop')->withTimestamps();
    }

    public function kitchens(): BelongsToMany
    {
        return $this->belongsToMany(Kitchen::class, 'supervisor_kitchen')->withTimestamps();
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'supervisor_driver')->withTimestamps();
    }

    public function userGroups(): MorphMany
    {
        return $this->morphMany(UserGroup::class, 'groupable');
    }

    // Filament
    public function getFilamentName(): string
    {
        return $this->name;
    }

    // Helpers
    public function managers(): BelongsToMany
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('slug', 'like', '%manager%');
        });
    }
}Modèle : Kitchenphp<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Kitchen extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'is_active',
        'operating_hours',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'operating_hours' => 'array',
            'capacity' => 'integer',
        ];
    }

    // Relations
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kitchen_user')->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_kitchen')->withTimestamps();
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'kitchen_driver')->withTimestamps();
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(Supervisor::class, 'supervisor_kitchen')->withTimestamps();
    }

    public function userGroups(): MorphMany
    {
        return $this->morphMany(UserGroup::class, 'groupable');
    }

    // Filament
    public function getFilamentName(): string
    {
        return $this->name;
    }

    // Helpers
    public function managers(): BelongsToMany
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('slug', 'like', '%manager%');
        });
    }
}Modèle : Driverphp<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Driver extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'is_active',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    // Relations
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'driver_user')->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_driver')->withTimestamps();
    }

    public function kitchens(): BelongsToMany
    {
        return $this->belongsToMany(Kitchen::class, 'kitchen_driver')->withTimestamps();
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(Supervisor::class, 'supervisor_driver')->withTimestamps();
    }

    public function userGroups(): MorphMany
    {
        return $this->morphMany(UserGroup::class, 'groupable');
    }

    // Filament
    public function getFilamentName(): string
    {
        return $this->name;
    }
}Modèle : DefaultPermissionTemplatephp<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DefaultPermissionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'scope_type',
        'scope_id',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Relations
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'template_roles')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'template_permissions')->withTimestamps();
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'template_user_groups')->withTimestamps();
    }

    // Apply template to user
    public function applyToUser(User $user, ?string $scopeType = null, ?int $scopeId = null): void
    {
        $effectiveScopeType = $scopeType ?? $this->scope_type;
        $effectiveScopeId = $scopeId ?? $this->scope_id;

        // Attach roles
        foreach ($this->roles as $role) {
            $user->roles()->attach($role->id, [
                'scope_type' => $effectiveScopeType,
                'scope_id' => $effectiveScopeId,
                'valid_from' => now(),
                'granted_by' => auth()->id(),
                'reason' => "Applied from template: {$this->name}",
            ]);
        }

        // Attach permissions
        foreach ($this->permissions as $permission) {
            $user->permissions()->attach($permission->id, [
                'permission_type' => 'grant',
                'scope_type' => $effectiveScopeType,
                'scope_id' => $effectiveScopeId,
                'valid_from' => now(),
                'granted_by' => auth()->id(),
                'reason' => "Applied from template: {$this->name}",
            ]);
        }

        // Attach user groups
        foreach ($this->userGroups as $group) {
            $user->userGroups()->attach($group->id, [
                'scope_type' => $effectiveScopeType,
                'scope_id' => $effectiveScopeId,
                'valid_from' => now(),
            ]);
        }
    }
}Modèle : PanelConfigurationphp<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'panel_id',
        'can_manage_users',
        'can_manage_roles',
        'can_manage_permissions',
        'can_invite_users',
        'can_assign_managers',
        'can_create_templates',
        'can_assign_templates',
        'can_view_own_permissions',
        'additional_settings',
    ];

    protected function casts(): array
    {
        return [
            'can_manage_users' => 'boolean',
            'can_manage_roles' => 'boolean',
            'can_manage_permissions' => 'boolean',
            'can_invite_users' => 'boolean',
            'can_assign_managers' => 'boolean',
            'can_create_templates' => 'boolean',
            'can_assign_templates' => 'boolean',
            'can_view_own_permissions' => 'boolean',
            'additional_settings' => 'array',
        ];
    }

    public static function getForPanel(string $panelId): ?self
    {
        return static::where('panel_id', $panelId)->first();
    }

    public function canPerform(string $capability): bool
    {
        return $this->getAttribute($capability) ?? false;
    }
}Extension du Modèle User (ajouts)php<?php

namespace App\Models;

// Dans le modèle User existant, ajouter ces relations et méthodes :

// Relations vers nouvelles entités
public function supervisors(): BelongsToMany
{
    return $this->belongsToMany(Supervisor::class, 'supervisor_user')->withTimestamps();
}

public function kitchens(): BelongsToMany
{
    return $this->belongsToMany(Kitchen::class, 'kitchen_user')->withTimestamps();
}

public function drivers(): BelongsToMany
{
    return $this->belongsToMany(Driver::class, 'driver_user')->withTimestamps();
}

// Méthodes getManaged pour chaque entité
public function getManagedSupervisors(): Collection
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return Supervisor::all();
    }

    $direct = $this->supervisors()->pluck('supervisors.id');
    $scoped = $this->roles()
        ->wherePivot('scope_type', 'supervisor')
        ->pluck('user_roles.scope_id')
        ->filter();

    return Supervisor::whereIn('id', $direct->merge($scoped)->unique())->get();
}

public function getManagedKitchens(): Collection
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return Kitchen::all();
    }

    $direct = $this->kitchens()->pluck('kitchens.id');
    $scoped = $this->roles()
        ->wherePivot('scope_type', 'kitchen')
        ->pluck('user_roles.scope_id')
        ->filter();

    return Kitchen::whereIn('id', $direct->merge($scoped)->unique())->get();
}

public function getManagedDrivers(): Collection
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return Driver::all();
    }

    $direct = $this->drivers()->pluck('drivers.id');
    $scoped = $this->roles()
        ->wherePivot('scope_type', 'driver')
        ->pluck('user_roles.scope_id')
        ->filter();

    return Driver::whereIn('id', $direct->merge($scoped)->unique())->get();
}

// Méthodes manages
public function managesSupervisor(int $id): bool
{
    return $this->getManagedSupervisors()->contains('id', $id);
}

public function managesKitchen(int $id): bool
{
    return $this->getManagedKitchens()->contains('id', $id);
}

public function managesDriver(int $id): bool
{
    return $this->getManagedDrivers()->contains('id', $id);
}

// Mise à jour canAccessPanel
public function canAccessPanel(Panel $panel): bool
{
    $panelId = $panel->getId();

    return match ($panelId) {
        'admin' => $this->hasAnyRole(['super_admin', 'admin']),
        'shop' => $this->hasAnyRole(['shop_manager', 'shop_staff']) || $this->shops()->exists(),
        'kitchen' => $this->hasAnyRole(['kitchen_manager', 'kitchen_staff']) || $this->kitchens()->exists(),
        'driver' => $this->hasRole('driver') || $this->drivers()->exists(),
        'supplier' => $this->hasAnyRole(['supplier_manager', 'supplier_staff']) || $this->suppliers()->exists(),
        'supervisor' => $this->hasAnyRole(['supervisor_manager', 'supervisor_staff']) || $this->supervisors()->exists(),
        default => false,
    };
}

// Mise à jour getTenants
public function getTenants(Panel $panel): Collection
{
    $panelId = $panel->getId();

    return match ($panelId) {
        'admin' => collect([]),
        'shop' => $this->hasAnyRole(['super_admin', 'admin']) ? Shop::all() : $this->shops,
        'kitchen' => $this->hasAnyRole(['super_admin', 'admin']) ? Kitchen::all() : $this->kitchens,
        'driver' => $this->hasAnyRole(['super_admin', 'admin']) ? Driver::all() : $this->drivers,
        'supplier' => $this->hasAnyRole(['super_admin', 'admin']) ? Supplier::all() : $this->suppliers,
        'supervisor' => $this->hasAnyRole(['super_admin', 'admin']) ? Supervisor::all() : $this->supervisors,
        default => collect([]),
    };
}

// Mise à jour canAccessTenant
public function canAccessTenant(Model $tenant): bool
{
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        return true;
    }

    if ($tenant instanceof Shop) {
        return $this->managesShop($tenant->id);
    }

    if ($tenant instanceof Kitchen) {
        return $this->managesKitchen($tenant->id);
    }

    if ($tenant instanceof Driver) {
        return $this->managesDriver($tenant->id);
    }

    if ($tenant instanceof Supplier) {
        return $this->managesSupplier($tenant->id);
    }

    if ($tenant instanceof Supervisor) {
        return $this->managesSupervisor($tenant->id);
    }

    return false;
}

// Nouvelle méthode getAccessiblePanels avec entités
public function getAccessiblePanels(): array
{
    $panels = [];

    // Admin Panel
    if ($this->hasAnyRole(['super_admin', 'admin'])) {
        $panels[] = [
            'id' => 'admin',
            'name' => 'Administration',
            'url' => '/admin',
            'icon' => 'heroicon-o-shield-check',
            'color' => 'danger',
            'entities' => [],
        ];
    }

    // Shop Panel
    $managedShops = $this->getManagedShops();
    if ($managedShops->isNotEmpty() || $this->hasAnyRole(['shop_manager', 'shop_staff'])) {
        $panels[] = [
            'id' => 'shop',
            'name' => 'Boutiques',
            'url' => '/shop',
            'icon' => 'heroicon-o-building-storefront',
            'color' => 'primary',
            'entities' => $managedShops->map(fn($shop) => [
                'id' => $shop->id,
                'name' => $shop->name,
                'url' => "/shop/{$shop->slug}",
                'linked_kitchens' => $shop->kitchens->pluck('name')->toArray(),
                'linked_drivers' => $shop->drivers->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    // Kitchen Panel
    $managedKitchens = $this->getManagedKitchens();
    if ($managedKitchens->isNotEmpty() || $this->hasAnyRole(['kitchen_manager', 'kitchen_staff'])) {
        $panels[] = [
            'id' => 'kitchen',
            'name' => 'Cuisines',
            'url' => '/kitchen',
            'icon' => 'heroicon-o-fire',
            'color' => 'warning',
            'entities' => $managedKitchens->map(fn($kitchen) => [
                'id' => $kitchen->id,
                'name' => $kitchen->name,
                'url' => "/kitchen/{$kitchen->slug}",
                'linked_shops' => $kitchen->shops->pluck('name')->toArray(),
                'linked_drivers' => $kitchen->drivers->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    // Driver Panel
    $managedDrivers = $this->getManagedDrivers();
    if ($managedDrivers->isNotEmpty() || $this->hasRole('driver')) {
        $panels[] = [
            'id' => 'driver',
            'name' => 'Livraisons',
            'url' => '/driver',
            'icon' => 'heroicon-o-truck',
            'color' => 'success',
            'entities' => $managedDrivers->map(fn($driver) => [
                'id' => $driver->id,
                'name' => $driver->name,
                'url' => "/driver/{$driver->slug}",
                'linked_shops' => $driver->shops->pluck('name')->toArray(),
                'linked_kitchens' => $driver->kitchens->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    // Supplier Panel
    $managedSuppliers = $this->getManagedSuppliers();
    if ($managedSuppliers->isNotEmpty() || $this->hasAnyRole(['supplier_manager', 'supplier_staff'])) {
        $panels[] = [
            'id' => 'supplier',
            'name' => 'Fournisseurs',
            'url' => '/supplier',
            'icon' => 'heroicon-o-cube',
            'color' => 'info',
            'entities' => $managedSuppliers->map(fn($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'url' => "/supplier/{$supplier->slug}",
            ])->toArray(),
        ];
    }

    // Supervisor Panel
    $managedSupervisors = $this->getManagedSupervisors();
    if ($managedSupervisors->isNotEmpty() || $this->hasAnyRole(['supervisor_manager', 'supervisor_staff'])) {
        $panels[] = [
            'id' => 'supervisor',
            'name' => 'Supervision',
            'url' => '/supervisor',
            'icon' => 'heroicon-o-eye',
            'color' => 'purple',
            'entities' => $managedSupervisors->map(fn($supervisor) => [
                'id' => $supervisor->id,
                'name' => $supervisor->name,
                'url' => "/supervisor/{$supervisor->slug}",
                'linked_shops' => $supervisor->shops->pluck('name')->toArray(),
                'linked_kitchens' => $supervisor->kitchens->pluck('name')->toArray(),
                'linked_drivers' => $supervisor->drivers->pluck('name')->toArray(),
            ])->toArray(),
        ];
    }

    return $panels;
}Étendre Shop Model (ajouts)php// Dans App\Models\Shop, ajouter :

public function kitchens(): BelongsToMany
{
    return $this->belongsToMany(Kitchen::class, 'shop_kitchen')->withTimestamps();
}

public function drivers(): BelongsToMany
{
    return $this->belongsToMany(Driver::class, 'shop_driver')->withTimestamps();
}

public function supervisors(): BelongsToMany
{
    return $this->belongsToMany(Supervisor::class, 'supervisor_shop')->withTimestamps();
}Étendre Supplier Model (ajouts)php// Dans App\Models\Supplier, ajouter si nécessaire :

public function supervisors(): BelongsToMany
{
    return $this->belongsToMany(Supervisor::class, 'supervisor_supplier')->withTimestamps();
}🎨 FILAMENT RESOURCES - ADMIN PANELUserResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8),
                        Forms\Components\Select::make('primary_role_id')
                            ->label('Primary Role')
                            ->relationship('primaryRole', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('primaryRole.name')
                    ->label('Primary Role')
                    ->badge()
                    ->color(fn (User $record) => $record->primaryRole?->color ?? 'gray'),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Additional Roles'),
                Tables\Columns\TextColumn::make('shops_count')
                    ->counts('shops')
                    ->label('Shops'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->counts('kitchens')
                    ->label('Kitchens'),
                Tables\Columns\TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('Drivers'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('primary_role_id')
                    ->relationship('primaryRole', 'name')
                    ->label('Primary Role'),
                Tables\Filters\Filter::make('has_shops')
                    ->query(fn (Builder $query): Builder => $query->has('shops'))
                    ->label('Has Shops'),
                Tables\Filters\Filter::make('has_kitchens')
                    ->query(fn (Builder $query): Builder => $query->has('kitchens'))
                    ->label('Has Kitchens'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\RolesRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
            RelationManagers\ShopsRelationManager::class,
            RelationManagers\KitchensRelationManager::class,
            RelationManagers\DriversRelationManager::class,
            RelationManagers\SuppliersRelationManager::class,
            RelationManagers\SupervisorsRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}RoleResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\TextInput::make('level')
                            ->numeric()
                            ->default(50)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Higher = More Authority'),
                        Forms\Components\ColorPicker::make('color')
                            ->default('gray'),
                        Forms\Components\Toggle::make('active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_system')
                            ->label('System Role')
                            ->disabled()
                            ->helperText('System roles cannot be deleted'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (Role $record) => $record->color),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 90 => 'danger',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users'),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_system')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active'),
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('System Role'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (Role $record) => $record->is_system),
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
            RelationManagers\PermissionsRelationManager::class,
            RelationManagers\UsersRelationManager::class,
            RelationManagers\ParentsRelationManager::class,
            RelationManagers\ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}ShopResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Filament\Resources\ShopResource\RelationManagers;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Entities Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Shop Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        Forms\Components\Textarea::make('address')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Managers'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->counts('kitchens')
                    ->label('Kitchens'),
                Tables\Columns\TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('Drivers'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UsersRelationManager::class,
            RelationManagers\KitchensRelationManager::class,
            RelationManagers\DriversRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'view' => Pages\ViewShop::route('/{record}'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}KitchenResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KitchenResource\Pages;
use App\Filament\Resources\KitchenResource\RelationManagers;
use App\Models\Kitchen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KitchenResource extends Resource
{
    protected static ?string $model = Kitchen::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationGroup = 'Entities Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kitchen Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        Forms\Components\Textarea::make('address')
                            ->rows(3),
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Managers'),
                Tables\Columns\TextColumn::make('shops_count')
                    ->counts('shops')
                    ->label('Linked Shops'),
                Tables\Columns\TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('Drivers'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UsersRelationManager::class,
            RelationManagers\ShopsRelationManager::class,
            RelationManagers\DriversRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKitchens::route('/'),
            'create' => Pages\CreateKitchen::route('/create'),
            'view' => Pages\ViewKitchen::route('/{record}'),
            'edit' => Pages\EditKitchen::route('/{record}/edit'),
        ];
    }
}DriverResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Entities Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Driver Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        Forms\Components\TextInput::make('vehicle_type')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('vehicle_number')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('license_number')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_available')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shops_count')
                    ->counts('shops')
                    ->label('Shops'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->counts('kitchens')
                    ->label('Kitchens'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_available'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UsersRelationManager::class,
            RelationManagers\ShopsRelationManager::class,
            RelationManagers\KitchensRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'view' => Pages\ViewDriver::route('/{record}'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}SupervisorResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervisorResource\Pages;
use App\Filament\Resources\SupervisorResource\RelationManagers;
use App\Models\Supervisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupervisorResource extends Resource
{
    protected static ?string $model = Supervisor::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Entities Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Supervisor Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        Forms\Components\Textarea::make('address')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Managers'),
                Tables\Columns\TextColumn::make('shops_count')
                    ->counts('shops')
                    ->label('Shops'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->counts('kitchens')
                    ->label('Kitchens'),
                Tables\Columns\TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('Drivers'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UsersRelationManager::class,
            RelationManagers\ShopsRelationManager::class,
            RelationManagers\KitchensRelationManager::class,
            RelationManagers\DriversRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupervisors::route('/'),
            'create' => Pages\CreateSupervisor::route('/create'),
            'view' => Pages\ViewSupervisor::route('/{record}'),
            'edit' => Pages\EditSupervisor::route('/{record}/edit'),
        ];
    }
}DefaultPermissionTemplateResource (Admin Panel)php<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DefaultPermissionTemplateResource\Pages;
use App\Filament\Resources\DefaultPermissionTemplateResource\RelationManagers;
use App\Models\DefaultPermissionTemplate;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Supplier;
use App\Models\Supervisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DefaultPermissionTemplateResource extends Resource
{
    protected static ?string $model = DefaultPermissionTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 4;

    protected static ?string $label = 'Permission Template';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\Select::make('scope_type')
                            ->options([
                                'global' => 'Global',
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->nullable()
                            ->reactive(),
                        Forms\Components\Select::make('scope_id')
                            ->options(function (callable $get) {
                                $type = $get('scope_type');
                                return match ($type) {
                                    'shop' => Shop::pluck('name', 'id'),
                                    'kitchen' => Kitchen::pluck('name', 'id'),
                                    'supplier' => Supplier::pluck('name', 'id'),
                                    'supervisor' => Supervisor::pluck('name', 'id'),
                                    default => [],
                                };
                            })
                            ->visible(fn (callable $get) => filled($get('scope_type')) && $get('scope_type') !== 'global')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Set as Default Template for Scope')
                            ->helperText('This template will be auto-applied when inviting users'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scope_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'global' => 'success',
                        'shop' => 'primary',
                        'kitchen' => 'warning',
                        'driver' => 'info',
                        'supplier' => 'purple',
                        'supervisor' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Roles'),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
                Tables\Columns\TextColumn::make('userGroups_count')
                    ->counts('userGroups')
                    ->label('Groups'),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scope_type')
                    ->options([
                        'global' => 'Global',
                        'shop' => 'Shop',
                        'kitchen' => 'Kitchen',
                        'driver' => 'Driver',
                        'supplier' => 'Supplier',
                        'supervisor' => 'Supervisor',
                    ]),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Template'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\RolesRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
            RelationManagers\UserGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDefaultPermissionTemplates::route('/'),
            'create' => Pages\CreateDefaultPermissionTemplate::route('/create'),
            'view' => Pages\ViewDefaultPermissionTemplate::route('/{record}'),
            'edit' => Pages\EditDefaultPermissionTemplate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $query;
        }

        // Filter by user's managed entities
        $panel = filament()->getCurrentPanel()->getId();

        return $query->where(function ($q) use ($user, $panel) {
            $q->whereNull('scope_type')
              ->orWhere(function ($sub) use ($user, $panel) {
                  $sub->where('scope_type', $panel);

                  $method = 'getManaged' . ucfirst($panel) . 's';
                  if (method_exists($user, $method)) {
                      $scopeIds = $user->$method()->pluck('id');
                      $sub->whereIn('scope_id', $scopeIds);
                  }
              });
        });
    }
}📦 RELATION MANAGERS DÉTAILLÉSRolesRelationManager (pour User)php<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $title = 'Roles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('recordId')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->color(fn ($record) => $record->color ?? 'gray')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.scope_type')
                    ->label('Scope Type')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('pivot.scope_id')
                    ->label('Scope ID'),
                Tables\Columns\TextColumn::make('pivot.valid_from')
                    ->label('Valid From')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('pivot.valid_until')
                    ->label('Valid Until')
                    ->dateTime()
                    ->placeholder('Forever'),
                Tables\Columns\TextColumn::make('pivot.reason')
                    ->label('Reason')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->pivot->reason),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('scope_type')
                            ->options([
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->nullable()
                            ->reactive(),
                        Forms\Components\Select::make('scope_id')
                            ->options(function (callable $get) {
                                $scopeType = $get('scope_type');
                                if (!$scopeType) return [];

                                $modelClass = 'App\\Models\\' . ucfirst($scopeType);
                                if (!class_exists($modelClass)) return [];

                                return $modelClass::pluck('name', 'id');
                            })
                            ->visible(fn (callable $get) => filled($get('scope_type')))
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->nullable(),
                        Forms\Components\Textarea::make('reason')
                            ->rows(3)
                            ->placeholder('Why is this role being assigned?'),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['granted_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No roles assigned')
            ->emptyStateDescription('Assign roles using the button above.')
            ->emptyStateIcon('heroicon-o-shield-check');
    }
}PermissionsRelationManager (pour User)php<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $title = 'Direct Permissions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.permission_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'grant' => 'success',
                        'revoke' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pivot.scope_type')
                    ->label('Scope Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('pivot.valid_from')
                    ->label('Valid From')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('pivot.reason')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->pivot->reason),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('permission_type')
                            ->options([
                                'grant' => 'Grant',
                                'revoke' => 'Revoke',
                            ])
                            ->default('grant')
                            ->required(),
                        Forms\Components\Select::make('scope_type')
                            ->options([
                                'shop' => 'Shop',
                                'kitchen' => 'Kitchen',
                                'driver' => 'Driver',
                                'supplier' => 'Supplier',
                                'supervisor' => 'Supervisor',
                            ])
                            ->nullable()
                            ->reactive(),
                        Forms\Components\Select::make('scope_id')
                            ->options(function (callable $get) {
                                $scopeType = $get('scope_type');
                                if (!$scopeType) return [];

                                $modelClass = 'App\\Models\\' . ucfirst($scopeType);
                                return $modelClass::pluck('name', 'id');
                            })
                            ->visible(fn (callable $get) => filled($get('scope_type')))
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->nullable(),
                        Forms\Components\Textarea::make('reason')
                            ->rows(3),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['granted_by'] = auth()->id();
                        return $data;
                    }),
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
}PermissionsRelationManager (pour Role) - avec Bulk Assignphp<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use App\Models\PermissionGroup;
use Filament\Forms;
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
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Add Permissions'),

                // Bulk Assign Action
                Tables\Actions\Action::make('bulk_assign')
                    ->label('Bulk Assign by Group')
                    ->icon('heroicon-o-squares-plus')
                    ->color('success')
                    ->form(function () {
                        $groups = PermissionGroup::with('permissions')->get();

                        return $groups->map(function ($group) {
                            return Forms\Components\CheckboxList::make('group_' . $group->id)
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

                        \Filament\Notifications\Notification::make()
                            ->title('Permissions added successfully')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('4xl'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No permissions assigned')
            ->emptyStateDescription('Use "Bulk Assign by Group" for quick setup')
            ->emptyStateIcon('heroicon-o-key');
    }
}ShopsRelationManager (pour User)php<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShopsRelationManager extends RelationManager
{
    protected static string $relationship = 'shops';

    protected static ?string $title = 'Managed Shops';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Add Shop'),
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
}KitchensRelationManager (Pattern réutilisable)php<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class KitchensRelationManager extends RelationManager
{
    protected static string $relationship = 'kitchens';

    protected static ?string $title = 'Managed Kitchens';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
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
}DriversRelationManager, SuppliersRelationManager, SupervisorsRelationManagerphp// Dupliquer le pattern ci-dessus pour:
// - DriversRelationManager
// - SuppliersRelationManager
// - SupervisorsRelationManager
// En changeant simplement le relationship et les colonnes affichées🎛️ PANEL PROVIDERSAdminPanelProviderphp<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}ShopPanelProviderphp<?php

namespace App\Providers\Filament;

use App\Models\Shop;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ShopPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('shop')
            ->path('shop')
            ->login()
            ->tenant(Shop::class)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Shop/Resources'), for: 'App\\Filament\\Shop\\Resources')
            ->discoverPages(in: app_path('Filament/Shop/Pages'), for: 'App\\Filament\\Shop\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Shop/Widgets'), for: 'App\\Filament\\Shop\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}KitchenPanelProviderphp<?php

namespace App\Providers\Filament;

use App\Models\Kitchen;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\
