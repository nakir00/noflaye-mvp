# 🎯 PROMPT - CONSOLIDATION SYSTÈME TYPE-SAFE

> **Mission** : Consolider le système de permissions avec Actions, Data objects, Enums type-safe et Laravel Policies
> **Packages utilisés** : spatie/laravel-data, lorisleiva/laravel-actions, Laravel Policies + système custom
> **Objectif** : Architecture propre, type-safe, testable et maintenable

---

## 📋 ANALYSE SYSTÈME ACTUEL

### **Existant dans le projet**

✅ **Packages installés** :
- spatie/laravel-data (DTOs)
- lorisleiva/laravel-actions (Actions)
- spatie/laravel-activitylog (Audit)
- spatie/laravel-query-builder (Queries)
- larastan/larastan (Static analysis)

✅ **Enums existants** :
- `app/Enums/AuditAction.php`
- `app/Enums/ConditionType.php`
- `app/Enums/PermissionAction.php`
- `app/Enums/WildcardPattern.php`

✅ **Services existants** :
- `app/Services/PermissionChecker.php`
- `app/Services/Permissions/ConditionEvaluator.php`
- `app/Services/Permissions/PermissionAnalytics.php`
- `app/Services/Permissions/PermissionApprovalWorkflow.php`
- `app/Services/Permissions/PermissionAuditLogger.php`
- `app/Services/Permissions/PermissionDelegator.php`
- `app/Services/Permissions/ScopeManager.php`
- `app/Services/Permissions/TemplateVersionManager.php`
- `app/Services/Permissions/WildcardExpander.php`

✅ **Actions existantes** :
- `app/Actions/AssignPermissionToUser.php`
- `app/Actions/RevokePermissionFromUser.php`

✅ **Data objects existants** :
- `app/Data/PermissionData.php`
- `app/Data/UserData.php`

❌ **Manquants** :
- Laravel Policies intégrant le système custom
- Enums complets pour toutes les permissions
- Data objects pour toutes les entités
- Actions pour toutes les opérations métier
- Type safety généralisé

---

## 🎯 OBJECTIFS DE CONSOLIDATION

### **1. ENUMS TYPE-SAFE**

Créer des enums pour **TOUS** les strings magiques :

#### **A. Permissions Enum**

**Fichier** : `app/Enums/Permission.php`

```php
<?php

namespace App\Enums;

enum Permission: string
{
    // ========================================
    // USER PERMISSIONS
    // ========================================
    case USER_VIEW_ANY = 'users.viewAny';
    case USER_VIEW = 'users.view';
    case USER_CREATE = 'users.create';
    case USER_UPDATE = 'users.update';
    case USER_DELETE = 'users.delete';
    case USER_RESTORE = 'users.restore';
    case USER_FORCE_DELETE = 'users.forceDelete';
    case USER_ASSIGN_TEMPLATE = 'users.assignTemplate';
    case USER_REVOKE_TEMPLATE = 'users.revokeTemplate';
    case USER_ASSIGN_PERMISSION = 'users.assignPermission';
    case USER_REVOKE_PERMISSION = 'users.revokePermission';

    // ========================================
    // PERMISSION MANAGEMENT
    // ========================================
    case PERMISSION_VIEW_ANY = 'permissions.viewAny';
    case PERMISSION_VIEW = 'permissions.view';
    case PERMISSION_CREATE = 'permissions.create';
    case PERMISSION_UPDATE = 'permissions.update';
    case PERMISSION_DELETE = 'permissions.delete';

    // ========================================
    // TEMPLATE MANAGEMENT
    // ========================================
    case TEMPLATE_VIEW_ANY = 'templates.viewAny';
    case TEMPLATE_VIEW = 'templates.view';
    case TEMPLATE_CREATE = 'templates.create';
    case TEMPLATE_UPDATE = 'templates.update';
    case TEMPLATE_DELETE = 'templates.delete';
    case TEMPLATE_ASSIGN = 'templates.assign';

    // ========================================
    // SHOP PERMISSIONS
    // ========================================
    case SHOP_VIEW_ANY = 'shops.viewAny';
    case SHOP_VIEW = 'shops.view';
    case SHOP_CREATE = 'shops.create';
    case SHOP_UPDATE = 'shops.update';
    case SHOP_DELETE = 'shops.delete';
    case SHOP_MANAGE_STAFF = 'shops.manageStaff';

    // ========================================
    // KITCHEN PERMISSIONS
    // ========================================
    case KITCHEN_VIEW_ANY = 'kitchens.viewAny';
    case KITCHEN_VIEW = 'kitchens.view';
    case KITCHEN_CREATE = 'kitchens.create';
    case KITCHEN_UPDATE = 'kitchens.update';
    case KITCHEN_DELETE = 'kitchens.delete';
    case KITCHEN_MANAGE_STAFF = 'kitchens.manageStaff';

    // ========================================
    // DRIVER PERMISSIONS
    // ========================================
    case DRIVER_VIEW_ANY = 'drivers.viewAny';
    case DRIVER_VIEW = 'drivers.view';
    case DRIVER_CREATE = 'drivers.create';
    case DRIVER_UPDATE = 'drivers.update';
    case DRIVER_DELETE = 'drivers.delete';
    case DRIVER_ASSIGN = 'drivers.assign';

    // ========================================
    // SUPERVISOR PERMISSIONS
    // ========================================
    case SUPERVISOR_VIEW_ANY = 'supervisors.viewAny';
    case SUPERVISOR_VIEW = 'supervisors.view';
    case SUPERVISOR_CREATE = 'supervisors.create';
    case SUPERVISOR_UPDATE = 'supervisors.update';
    case SUPERVISOR_DELETE = 'supervisors.delete';
    case SUPERVISOR_ASSIGN = 'supervisors.assign';

    // ========================================
    // SUPPLIER PERMISSIONS
    // ========================================
    case SUPPLIER_VIEW_ANY = 'suppliers.viewAny';
    case SUPPLIER_VIEW = 'suppliers.view';
    case SUPPLIER_CREATE = 'suppliers.create';
    case SUPPLIER_UPDATE = 'suppliers.update';
    case SUPPLIER_DELETE = 'suppliers.delete';
    case SUPPLIER_MANAGE = 'suppliers.manage';

    // ========================================
    // DELEGATION PERMISSIONS
    // ========================================
    case DELEGATION_VIEW_ANY = 'delegations.viewAny';
    case DELEGATION_VIEW = 'delegations.view';
    case DELEGATION_CREATE = 'delegations.create';
    case DELEGATION_UPDATE = 'delegations.update';
    case DELEGATION_DELETE = 'delegations.delete';
    case DELEGATION_APPROVE = 'delegations.approve';
    case DELEGATION_REJECT = 'delegations.reject';

    // ========================================
    // REQUEST PERMISSIONS
    // ========================================
    case REQUEST_VIEW_ANY = 'requests.viewAny';
    case REQUEST_VIEW = 'requests.view';
    case REQUEST_CREATE = 'requests.create';
    case REQUEST_UPDATE = 'requests.update';
    case REQUEST_DELETE = 'requests.delete';
    case REQUEST_APPROVE = 'requests.approve';
    case REQUEST_REJECT = 'requests.reject';

    // ========================================
    // AUDIT PERMISSIONS
    // ========================================
    case AUDIT_VIEW_ANY = 'audit.viewAny';
    case AUDIT_VIEW = 'audit.view';
    case AUDIT_EXPORT = 'audit.export';

    // ========================================
    // WILDCARD PERMISSIONS
    // ========================================
    case WILDCARD_VIEW_ANY = 'wildcards.viewAny';
    case WILDCARD_VIEW = 'wildcards.view';
    case WILDCARD_CREATE = 'wildcards.create';
    case WILDCARD_UPDATE = 'wildcards.update';
    case WILDCARD_DELETE = 'wildcards.delete';

    // ========================================
    // SCOPE PERMISSIONS
    // ========================================
    case SCOPE_VIEW_ANY = 'scopes.viewAny';
    case SCOPE_VIEW = 'scopes.view';
    case SCOPE_CREATE = 'scopes.create';
    case SCOPE_UPDATE = 'scopes.update';
    case SCOPE_DELETE = 'scopes.delete';

    /**
     * Get all permissions for a resource
     */
    public static function forResource(string $resource): array
    {
        return array_filter(
            self::cases(),
            fn(self $case) => str_starts_with($case->value, $resource . '.')
        );
    }

    /**
     * Check if permission is for a specific action
     */
    public function isAction(string $action): bool
    {
        return str_ends_with($this->value, '.' . $action);
    }

    /**
     * Get resource name
     */
    public function resource(): string
    {
        return explode('.', $this->value)[0];
    }

    /**
     * Get action name
     */
    public function action(): string
    {
        $parts = explode('.', $this->value);
        return end($parts);
    }
}
```

#### **B. Template Enum**

**Fichier** : `app/Enums/Template.php`

```php
<?php

namespace App\Enums;

enum Template: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case SHOP_MANAGER = 'shop_manager';
    case SHOP_STAFF = 'shop_staff';
    case KITCHEN_MANAGER = 'kitchen_manager';
    case KITCHEN_STAFF = 'kitchen_staff';
    case DRIVER = 'driver';
    case SUPERVISOR = 'supervisor';
    case SUPPLIER_MANAGER = 'supplier_manager';
    case CUSTOMER = 'customer';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::SHOP_MANAGER => 'Gérant de Boutique',
            self::SHOP_STAFF => 'Staff Boutique',
            self::KITCHEN_MANAGER => 'Chef de Cuisine',
            self::KITCHEN_STAFF => 'Staff Cuisine',
            self::DRIVER => 'Chauffeur',
            self::SUPERVISOR => 'Superviseur',
            self::SUPPLIER_MANAGER => 'Gestionnaire Fournisseur',
            self::CUSTOMER => 'Client',
        };
    }

    /**
     * Get default permissions for template
     */
    public function defaultPermissions(): array
    {
        return match($this) {
            self::SUPER_ADMIN => Permission::cases(),
            self::ADMIN => [
                Permission::USER_VIEW_ANY,
                Permission::USER_VIEW,
                Permission::USER_CREATE,
                Permission::USER_UPDATE,
                Permission::SHOP_VIEW_ANY,
                Permission::KITCHEN_VIEW_ANY,
                // ... autres permissions
            ],
            self::SHOP_MANAGER => [
                Permission::SHOP_VIEW,
                Permission::SHOP_UPDATE,
                Permission::SHOP_MANAGE_STAFF,
                // ...
            ],
            default => [],
        };
    }

    /**
     * Check if template is admin-level
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    /**
     * Check if template can manage entity
     */
    public function canManage(string $entity): bool
    {
        return match($this) {
            self::SUPER_ADMIN, self::ADMIN => true,
            self::SHOP_MANAGER => $entity === 'shop',
            self::KITCHEN_MANAGER => $entity === 'kitchen',
            default => false,
        };
    }
}
```

#### **C. Autres Enums nécessaires**

**Fichier** : `app/Enums/EntityType.php`

```php
<?php

namespace App\Enums;

enum EntityType: string
{
    case USER = 'user';
    case SHOP = 'shop';
    case KITCHEN = 'kitchen';
    case DRIVER = 'driver';
    case SUPERVISOR = 'supervisor';
    case SUPPLIER = 'supplier';
    case PERMISSION = 'permission';
    case TEMPLATE = 'template';
    case DELEGATION = 'delegation';
    case REQUEST = 'request';

    public function modelClass(): string
    {
        return match($this) {
            self::USER => \App\Models\User::class,
            self::SHOP => \App\Models\Shop::class,
            self::KITCHEN => \App\Models\Kitchen::class,
            self::DRIVER => \App\Models\Driver::class,
            self::SUPERVISOR => \App\Models\Supervisor::class,
            self::SUPPLIER => \App\Models\Supplier::class,
            self::PERMISSION => \App\Models\Permission::class,
            self::TEMPLATE => \App\Models\PermissionTemplate::class,
            self::DELEGATION => \App\Models\PermissionDelegation::class,
            self::REQUEST => \App\Models\PermissionRequest::class,
        };
    }
}
```

**Fichier** : `app/Enums/RequestStatus.php`

```php
<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::CANCELLED => 'Annulé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::CANCELLED]);
    }
}
```

---

### **2. DATA OBJECTS (SPATIE/LARAVEL-DATA)**

Créer des DTOs pour **toutes** les entités métier :

#### **A. Permission DTOs**

**Fichier** : `app/Data/Permissions/PermissionData.php`

```php
<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;

class PermissionData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,

        #[Required, StringType, Max(255)]
        public string $slug,

        public ?string $description,

        #[Required]
        public int $permission_group_id,

        public bool $is_active = true,

        public bool $is_system = false,
    ) {}

    /**
     * Create from enum
     */
    public static function fromEnum(PermissionEnum $permission): self
    {
        return new self(
            name: ucfirst(str_replace(['.', '_'], ' ', $permission->value)),
            slug: $permission->value,
            description: "Permission to {$permission->action()} {$permission->resource()}",
            permission_group_id: 1, // Default group
            is_active: true,
            is_system: true,
        );
    }
}
```

**Fichier** : `app/Data/Permissions/AssignPermissionData.php`

```php
<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Carbon\Carbon;

class AssignPermissionData extends Data
{
    public function __construct(
        #[Required]
        public int $user_id,

        #[Required]
        public PermissionEnum $permission,

        public ?int $scope_id = null,

        public ?Carbon $valid_from = null,

        public ?Carbon $valid_until = null,

        public string $source = 'direct',

        public ?string $reason = null,
    ) {}
}
```

**Fichier** : `app/Data/Permissions/PermissionCheckData.php`

```php
<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Spatie\LaravelData\Data;

class PermissionCheckData extends Data
{
    public function __construct(
        public int $user_id,
        public PermissionEnum $permission,
        public ?int $scope_id = null,
        public array $context = [],
    ) {}
}
```

#### **B. Template DTOs**

**Fichier** : `app/Data/Templates/TemplateData.php`

```php
<?php

namespace App\Data\Templates;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;

class TemplateData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        public string $slug,

        public ?string $description = null,

        public ?int $parent_id = null,

        public int $level = 0,

        public bool $is_active = true,

        public bool $is_system = false,

        public bool $auto_sync_users = false,
    ) {}

    /**
     * Create from enum
     */
    public static function fromEnum(TemplateEnum $template): self
    {
        return new self(
            name: $template->label(),
            slug: $template->value,
            description: "Template: {$template->label()}",
            is_active: true,
            is_system: true,
        );
    }
}
```

**Fichier** : `app/Data/Templates/AssignTemplateData.php`

```php
<?php

namespace App\Data\Templates;

use App\Enums\Template as TemplateEnum;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class AssignTemplateData extends Data
{
    public function __construct(
        public int $user_id,
        public TemplateEnum $template,
        public bool $auto_sync = true,
        public ?Carbon $valid_from = null,
        public ?Carbon $valid_until = null,
    ) {}
}
```

#### **C. User DTOs**

**Fichier** : `app/Data/Users/UserData.php` (améliorer l'existant)

```php
<?php

namespace App\Data\Users;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;

class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required, Email]
        public string $email,

        public ?TemplateEnum $primary_template = null,

        public ?string $phone = null,

        public bool $is_active = true,
    ) {}
}
```

**Fichier** : `app/Data/Users/UserRegistrationData.php`

```php
<?php

namespace App\Data\Users;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;

class UserRegistrationData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required, Email]
        public string $email,

        #[Required, Min(8)]
        public string $password,

        public ?TemplateEnum $initial_template = null,

        public ?string $phone = null,
    ) {}
}
```

#### **D. Business Entity DTOs**

**Fichier** : `app/Data/Shops/ShopData.php`

```php
<?php

namespace App\Data\Shops;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;

class ShopData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        public string $slug,

        public ?string $description = null,

        public ?string $address = null,

        public ?string $phone = null,

        public bool $is_active = true,
    ) {}
}
```

**Fichier** : `app/Data/Kitchens/KitchenData.php`

```php
<?php

namespace App\Data\Kitchens;

use Spatie\LaravelData\Data;

class KitchenData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $address = null,
        public bool $is_active = true,
    ) {}
}
```

**Similar for** : Driver, Supervisor, Supplier

---

### **3. LARAVEL ACTIONS (LORISLEIVA)**

Créer des actions pour **toutes** les opérations métier :

#### **A. Permission Actions**

**Fichier** : `app/Actions/Permissions/AssignPermissionToUser.php` (améliorer existant)

```php
<?php

namespace App\Actions\Permissions;

use App\Data\Permissions\AssignPermissionData;
use App\Enums\AuditAction;
use App\Models\Permission;
use App\Models\User;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignPermissionToUser
{
    use AsAction;

    public function __construct(
        private PermissionChecker $checker
    ) {}

    /**
     * Assign permission to user with full validation
     */
    public function handle(AssignPermissionData $data): bool
    {
        return DB::transaction(function () use ($data) {
            $user = User::findOrFail($data->user_id);
            $permission = Permission::where('slug', $data->permission->value)->firstOrFail();

            // Check if already assigned
            $exists = $user->permissions()
                ->where('permission_id', $permission->id)
                ->where('scope_id', $data->scope_id)
                ->exists();

            if ($exists) {
                return false;
            }

            // Attach permission
            $user->permissions()->attach($permission->id, [
                'scope_id' => $data->scope_id,
                'source' => $data->source,
                'granted_at' => now(),
                'valid_from' => $data->valid_from,
                'valid_until' => $data->valid_until,
                'reason' => $data->reason,
            ]);

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'permission' => $data->permission->value,
                    'permission_name' => $permission->name,
                    'scope_id' => $data->scope_id,
                    'source' => $data->source,
                    'reason' => $data->reason,
                ])
                ->log(AuditAction::GRANTED->value);

            // Clear cache
            Cache::tags(['users', "user.{$user->id}", 'permissions'])->flush();

            return true;
        });
    }

    /**
     * Use as controller
     */
    public function asController(AssignPermissionRequest $request)
    {
        $data = AssignPermissionData::from($request->validated());
        $result = $this->handle($data);

        return response()->json([
            'success' => $result,
            'message' => $result
                ? 'Permission assigned successfully'
                : 'Permission already assigned',
        ]);
    }

    /**
     * Use as job
     */
    public function asJob(AssignPermissionData $data)
    {
        $this->handle($data);
    }
}
```

**Fichier** : `app/Actions/Permissions/RevokePermissionFromUser.php`

```php
<?php

namespace App\Actions\Permissions;

use App\Enums\AuditAction;
use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RevokePermissionFromUser
{
    use AsAction;

    public function handle(
        int $userId,
        PermissionEnum $permission,
        ?int $scopeId = null,
        ?string $reason = null
    ): bool {
        return DB::transaction(function () use ($userId, $permission, $scopeId, $reason) {
            $user = User::findOrFail($userId);
            $permissionModel = Permission::where('slug', $permission->value)->firstOrFail();

            $detached = $user->permissions()
                ->wherePivot('permission_id', $permissionModel->id)
                ->wherePivot('scope_id', $scopeId)
                ->detach();

            if ($detached) {
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'permission' => $permission->value,
                        'scope_id' => $scopeId,
                        'reason' => $reason,
                    ])
                    ->log(AuditAction::REVOKED->value);

                Cache::tags(['users', "user.{$user->id}", 'permissions'])->flush();
            }

            return $detached > 0;
        });
    }
}
```

**Fichier** : `app/Actions/Permissions/CheckUserPermission.php`

```php
<?php

namespace App\Actions\Permissions;

use App\Data\Permissions\PermissionCheckData;
use App\Services\Permissions\PermissionChecker;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckUserPermission
{
    use AsAction;

    public function __construct(
        private PermissionChecker $checker
    ) {}

    public function handle(PermissionCheckData $data): bool
    {
        return $this->checker->userHasPermission(
            userId: $data->user_id,
            permission: $data->permission->value,
            scopeId: $data->scope_id,
            context: $data->context
        );
    }
}
```

#### **B. Template Actions**

**Fichier** : `app/Actions/Templates/AssignTemplateToUser.php`

```php
<?php

namespace App\Actions\Templates;

use App\Data\Templates\AssignTemplateData;
use App\Models\PermissionTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignTemplateToUser
{
    use AsAction;

    public function handle(AssignTemplateData $data): bool
    {
        return DB::transaction(function () use ($data) {
            $user = User::findOrFail($data->user_id);
            $template = PermissionTemplate::where('slug', $data->template->value)
                ->firstOrFail();

            // Check if already assigned
            if ($user->templates()->where('id', $template->id)->exists()) {
                return false;
            }

            // Attach template
            $user->templates()->attach($template->id, [
                'auto_sync' => $data->auto_sync,
                'valid_from' => $data->valid_from ?? now(),
                'valid_until' => $data->valid_until,
            ]);

            // Set as primary if none exists
            if (!$user->primary_template_id) {
                $user->update(['primary_template_id' => $template->id]);
            }

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'template' => $data->template->value,
                    'template_name' => $template->name,
                ])
                ->log('template_assigned');

            Cache::tags(['users', "user.{$user->id}", 'templates'])->flush();

            return true;
        });
    }
}
```

#### **C. Business Entity Actions**

**Fichier** : `app/Actions/Shops/CreateShop.php`

```php
<?php

namespace App\Actions\Shops;

use App\Data\Shops\ShopData;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateShop
{
    use AsAction;

    public function handle(ShopData $data): Shop
    {
        return DB::transaction(function () use ($data) {
            $shop = Shop::create($data->toArray());

            activity()
                ->performedOn($shop)
                ->causedBy(auth()->user())
                ->log('shop_created');

            return $shop;
        });
    }

    public function asController(CreateShopRequest $request)
    {
        $data = ShopData::from($request->validated());
        $shop = $this->handle($data);

        return redirect()
            ->route('shops.show', $shop)
            ->with('success', 'Shop created successfully');
    }
}
```

**Similar for** : UpdateShop, DeleteShop, RestoreShop

---

### **4. LARAVEL POLICIES INTÉGRANT LE SYSTÈME CUSTOM**

Créer des policies utilisant le PermissionChecker :

#### **A. Base Policy Trait**

**Fichier** : `app/Policies/Concerns/ChecksPermissions.php`

```php
<?php

namespace App\Policies\Concerns;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use App\Services\Permissions\PermissionChecker;

trait ChecksPermissions
{
    /**
     * Check if user has permission
     */
    protected function can(User $user, PermissionEnum $permission, ?int $scopeId = null): bool
    {
        return app(PermissionChecker::class)->userHasPermission(
            userId: $user->id,
            permission: $permission->value,
            scopeId: $scopeId
        );
    }

    /**
     * Check if user has any of the permissions
     */
    protected function canAny(User $user, array $permissions, ?int $scopeId = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($user, $permission, $scopeId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all permissions
     */
    protected function canAll(User $user, array $permissions, ?int $scopeId = null): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($user, $permission, $scopeId)) {
                return false;
            }
        }
        return true;
    }
}
```

#### **B. User Policy**

**Fichier** : `app/Policies/UserPolicy.php`

```php
<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class UserPolicy
{
    use ChecksPermissions;

    /**
     * Determine if user can view any users
     */
    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::USER_VIEW_ANY);
    }

    /**
     * Determine if user can view the model
     */
    public function view(User $user, User $model): bool
    {
        // Can view self
        if ($user->id === $model->id) {
            return true;
        }

        return $this->can($user, Permission::USER_VIEW);
    }

    /**
     * Determine if user can create users
     */
    public function create(User $user): bool
    {
        return $this->can($user, Permission::USER_CREATE);
    }

    /**
     * Determine if user can update the model
     */
    public function update(User $user, User $model): bool
    {
        // Cannot update self via this permission
        if ($user->id === $model->id) {
            return false;
        }

        return $this->can($user, Permission::USER_UPDATE);
    }

    /**
     * Determine if user can delete the model
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        return $this->can($user, Permission::USER_DELETE);
    }

    /**
     * Determine if user can restore the model
     */
    public function restore(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_RESTORE);
    }

    /**
     * Determine if user can permanently delete
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_FORCE_DELETE);
    }

    /**
     * Determine if user can assign templates
     */
    public function assignTemplate(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_ASSIGN_TEMPLATE);
    }

    /**
     * Determine if user can assign permissions
     */
    public function assignPermission(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_ASSIGN_PERMISSION);
    }
}
```

#### **C. Shop Policy**

**Fichier** : `app/Policies/ShopPolicy.php`

```php
<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Shop;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class ShopPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::SHOP_VIEW_ANY);
    }

    public function view(User $user, Shop $shop): bool
    {
        // Check global permission
        if ($this->can($user, Permission::SHOP_VIEW)) {
            return true;
        }

        // Check scoped permission (user manages this shop)
        return $this->can($user, Permission::SHOP_VIEW, $shop->id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::SHOP_CREATE);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_UPDATE)
            || $this->can($user, Permission::SHOP_UPDATE, $shop->id);
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_DELETE);
    }

    public function manageStaff(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_MANAGE_STAFF, $shop->id);
    }
}
```

**Similar policies for** : Kitchen, Driver, Supervisor, Supplier, Permission, Template, Delegation, Request

#### **D. Register Policies**

**Fichier** : `app/Providers/AuthServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Driver;
use App\Models\Supervisor;
use App\Models\Supplier;
use App\Models\Permission;
use App\Models\PermissionTemplate;
use App\Policies\UserPolicy;
use App\Policies\ShopPolicy;
use App\Policies\KitchenPolicy;
use App\Policies\DriverPolicy;
use App\Policies\SupervisorPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\TemplatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Shop::class => ShopPolicy::class,
        Kitchen::class => KitchenPolicy::class,
        Driver::class => DriverPolicy::class,
        Supervisor::class => SupervisorPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Permission::class => PermissionPolicy::class,
        PermissionTemplate::class => TemplatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services
     */
    public function boot(): void
    {
        //
    }
}
```

---

### **5. MISE À JOUR DES SERVICES EXISTANTS**

Adapter les services pour utiliser les enums :

#### **A. PermissionChecker.php**

```php
<?php

namespace App\Services\Permissions;

use App\Enums\Permission as PermissionEnum;
use Illuminate\Support\Facades\Cache;

class PermissionChecker
{
    /**
     * Check if user has permission (accepte string ou enum)
     */
    public function userHasPermission(
        int $userId,
        string|PermissionEnum $permission,
        ?int $scopeId = null,
        array $context = []
    ): bool {
        $permissionSlug = $permission instanceof PermissionEnum
            ? $permission->value
            : $permission;

        return Cache::tags(['permissions', "user.{$userId}"])
            ->remember(
                "permission.{$userId}.{$permissionSlug}.{$scopeId}",
                3600,
                fn() => $this->checkPermission($userId, $permissionSlug, $scopeId, $context)
            );
    }

    // ... rest of implementation
}
```

---

### **6. UTILISATION DANS FILAMENT RESOURCES**

#### **Exemple avec UserResource**

```php
<?php

namespace App\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', User::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', User::class);
    }

    // ... rest of resource
}
```

---

## 📋 PLAN D'IMPLÉMENTATION

### **Phase 1 : Enums (2-3h)**
1. Créer `Permission` enum avec TOUTES les permissions
2. Créer `Template` enum
3. Créer `EntityType` enum
4. Créer `RequestStatus` enum
5. Mettre à jour les enums existants si nécessaire

### **Phase 2 : Data Objects (3-4h)**
1. Créer DTOs pour Permissions (PermissionData, AssignPermissionData, etc.)
2. Créer DTOs pour Templates
3. Créer DTOs pour Users (améliorer existants)
4. Créer DTOs pour Business entities (Shop, Kitchen, Driver, etc.)
5. Créer DTOs pour Requests/Delegations

### **Phase 3 : Actions (4-5h)**
1. Améliorer actions Permission existantes
2. Créer actions Template (Assign, Revoke, etc.)
3. Créer actions CRUD pour chaque entité (Create, Update, Delete, Restore)
4. Créer actions métier spécifiques (ApproveRequest, DelegatePermission, etc.)

### **Phase 4 : Policies (3-4h)**
1. Créer trait `ChecksPermissions`
2. Créer policies pour chaque modèle (User, Shop, Kitchen, etc.)
3. Register policies dans AuthServiceProvider
4. Tester intégration avec Filament

### **Phase 5 : Services Update (2-3h)**
1. Adapter PermissionChecker pour accepter enums
2. Mettre à jour autres services si nécessaire
3. Remplacer strings magiques par enums partout

### **Phase 6 : Tests (3-4h)**
1. Tests unitaires pour chaque Action
2. Tests d'intégration pour Policies
3. Tests Pest pour DTOs
4. Validation Larastan

**Total estimé** : ~18-23h

---

## 📊 STRUCTURE FINALE

```
app/
├── Actions/
│   ├── Permissions/
│   │   ├── AssignPermissionToUser.php
│   │   ├── RevokePermissionFromUser.php
│   │   ├── CheckUserPermission.php
│   │   └── SyncTemplatePermissions.php
│   ├── Templates/
│   │   ├── AssignTemplateToUser.php
│   │   ├── RevokeTemplateFromUser.php
│   │   └── SetPrimaryTemplate.php
│   ├── Shops/
│   │   ├── CreateShop.php
│   │   ├── UpdateShop.php
│   │   ├── DeleteShop.php
│   │   └── AssignStaffToShop.php
│   └── ... (similar for other entities)
│
├── Data/
│   ├── Permissions/
│   │   ├── PermissionData.php
│   │   ├── AssignPermissionData.php
│   │   ├── PermissionCheckData.php
│   │   └── RevokePermissionData.php
│   ├── Templates/
│   │   ├── TemplateData.php
│   │   └── AssignTemplateData.php
│   ├── Users/
│   │   ├── UserData.php
│   │   └── UserRegistrationData.php
│   ├── Shops/
│   │   └── ShopData.php
│   └── ... (similar for other entities)
│
├── Enums/
│   ├── Permission.php ⭐ NOUVEAU
│   ├── Template.php ⭐ NOUVEAU
│   ├── EntityType.php ⭐ NOUVEAU
│   ├── RequestStatus.php ⭐ NOUVEAU
│   ├── AuditAction.php ✅ EXISTANT
│   ├── ConditionType.php ✅ EXISTANT
│   ├── PermissionAction.php ✅ EXISTANT
│   └── WildcardPattern.php ✅ EXISTANT
│
├── Policies/
│   ├── Concerns/
│   │   └── ChecksPermissions.php ⭐ NOUVEAU
│   ├── UserPolicy.php ⭐ NOUVEAU
│   ├── ShopPolicy.php ⭐ NOUVEAU
│   ├── KitchenPolicy.php ⭐ NOUVEAU
│   ├── DriverPolicy.php ⭐ NOUVEAU
│   ├── SupervisorPolicy.php ⭐ NOUVEAU
│   ├── SupplierPolicy.php ⭐ NOUVEAU
│   ├── PermissionPolicy.php ⭐ NOUVEAU
│   └── TemplatePolicy.php ⭐ NOUVEAU
│
└── Services/
    └── Permissions/
        ├── PermissionChecker.php ✏️ UPDATE (accepter enums)
        └── ... (autres services)
```

---

## ✅ CHECKLIST

### **Enums**
- [ ] `Permission` enum avec toutes les permissions
- [ ] `Template` enum
- [ ] `EntityType` enum
- [ ] `RequestStatus` enum
- [ ] Mettre à jour enums existants si nécessaire

### **Data Objects**
- [ ] Permission DTOs (PermissionData, AssignPermissionData, PermissionCheckData)
- [ ] Template DTOs
- [ ] User DTOs (améliorer existants)
- [ ] Shop, Kitchen, Driver, Supervisor, Supplier DTOs
- [ ] Request/Delegation DTOs

### **Actions**
- [ ] Permission actions (Assign, Revoke, Check, Sync)
- [ ] Template actions (Assign, Revoke, SetPrimary)
- [ ] CRUD actions pour chaque entité
- [ ] Business actions (Approve, Reject, Delegate, etc.)

### **Policies**
- [ ] Trait ChecksPermissions
- [ ] UserPolicy
- [ ] ShopPolicy, KitchenPolicy, DriverPolicy
- [ ] SupervisorPolicy, SupplierPolicy
- [ ] PermissionPolicy, TemplatePolicy
- [ ] Register dans AuthServiceProvider

### **Services**
- [ ] Adapter PermissionChecker pour enums
- [ ] Update autres services si nécessaire

### **Tests**
- [ ] Tests Actions (Pest)
- [ ] Tests Policies
- [ ] Tests DTOs validation
- [ ] Larastan static analysis

---

## 🎯 EXEMPLES D'UTILISATION FINALE

### **Dans un Controller**

```php
use App\Actions\Permissions\AssignPermissionToUser;
use App\Data\Permissions\AssignPermissionData;
use App\Enums\Permission;

// Type-safe, validé, loggé, caché
AssignPermissionToUser::run(
    AssignPermissionData::from([
        'user_id' => $user->id,
        'permission' => Permission::SHOP_UPDATE,
        'scope_id' => $shop->id,
        'reason' => 'Promoted to shop manager',
    ])
);
```

### **Dans une Policy**

```php
public function update(User $user, Shop $shop): bool
{
    return $this->can($user, Permission::SHOP_UPDATE, $shop->id);
}
```

### **Dans Filament**

```php
public static function canCreate(): bool
{
    return auth()->user()->can('create', Shop::class);
}
```

### **Dans un Service**

```php
use App\Enums\Permission;

if ($checker->userHasPermission($userId, Permission::USER_DELETE)) {
    // ...
}
```

---

**Architecture propre, type-safe, testable et maintenable ! 🚀**
