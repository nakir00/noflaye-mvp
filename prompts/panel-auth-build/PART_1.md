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
};
