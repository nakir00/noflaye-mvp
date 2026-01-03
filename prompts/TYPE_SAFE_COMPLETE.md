# 🎯 PROMPT CLAUDE CODE - SYSTÈME TYPE-SAFE COMPLET & OPTIMISÉ

> **Mission** : Implémenter un système de permissions type-safe, performant et production-ready
> **Durée estimée** : 20-25h
> **Packages** : spatie/laravel-data, lorisleiva/laravel-actions, Laravel Policies

---

## 📋 PHASE 1 : ENUMS TYPE-SAFE AVEC FALLBACK DYNAMIQUE (3h)

### **1.1 Permission Enum Principal**

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
    case SHOP_RESTORE = 'shops.restore';
    case SHOP_FORCE_DELETE = 'shops.forceDelete';
    case SHOP_MANAGE_STAFF = 'shops.manageStaff';
    case SHOP_ARCHIVE = 'shops.archive';
    
    // ========================================
    // KITCHEN PERMISSIONS
    // ========================================
    case KITCHEN_VIEW_ANY = 'kitchens.viewAny';
    case KITCHEN_VIEW = 'kitchens.view';
    case KITCHEN_CREATE = 'kitchens.create';
    case KITCHEN_UPDATE = 'kitchens.update';
    case KITCHEN_DELETE = 'kitchens.delete';
    case KITCHEN_RESTORE = 'kitchens.restore';
    case KITCHEN_FORCE_DELETE = 'kitchens.forceDelete';
    case KITCHEN_MANAGE_STAFF = 'kitchens.manageStaff';
    
    // ========================================
    // DRIVER PERMISSIONS
    // ========================================
    case DRIVER_VIEW_ANY = 'drivers.viewAny';
    case DRIVER_VIEW = 'drivers.view';
    case DRIVER_CREATE = 'drivers.create';
    case DRIVER_UPDATE = 'drivers.update';
    case DRIVER_DELETE = 'drivers.delete';
    case DRIVER_RESTORE = 'drivers.restore';
    case DRIVER_FORCE_DELETE = 'drivers.forceDelete';
    case DRIVER_ASSIGN = 'drivers.assign';
    case DRIVER_UNASSIGN = 'drivers.unassign';
    
    // ========================================
    // SUPERVISOR PERMISSIONS
    // ========================================
    case SUPERVISOR_VIEW_ANY = 'supervisors.viewAny';
    case SUPERVISOR_VIEW = 'supervisors.view';
    case SUPERVISOR_CREATE = 'supervisors.create';
    case SUPERVISOR_UPDATE = 'supervisors.update';
    case SUPERVISOR_DELETE = 'supervisors.delete';
    case SUPERVISOR_RESTORE = 'supervisors.restore';
    case SUPERVISOR_FORCE_DELETE = 'supervisors.forceDelete';
    case SUPERVISOR_ASSIGN = 'supervisors.assign';
    
    // ========================================
    // SUPPLIER PERMISSIONS
    // ========================================
    case SUPPLIER_VIEW_ANY = 'suppliers.viewAny';
    case SUPPLIER_VIEW = 'suppliers.view';
    case SUPPLIER_CREATE = 'suppliers.create';
    case SUPPLIER_UPDATE = 'suppliers.update';
    case SUPPLIER_DELETE = 'suppliers.delete';
    case SUPPLIER_RESTORE = 'suppliers.restore';
    case SUPPLIER_FORCE_DELETE = 'suppliers.forceDelete';
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

    // ========================================
    // HELPER METHODS
    // ========================================

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
     * Try to create from string value (with fallback for custom permissions)
     */
    public static function tryFromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Check if permission exists in enum
     */
    public static function exists(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    /**
     * Get or create from string (fallback to custom)
     */
    public static function fromString(string $value): self|string
    {
        return self::tryFrom($value) ?? $value; // Return string if custom
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

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return ucfirst(str_replace(['.', '_'], ' ', $this->value));
    }

    /**
     * Check if it's a "viewAny" permission
     */
    public function isViewAny(): bool
    {
        return $this->isAction('viewAny');
    }

    /**
     * Check if it's a destructive action (delete, forceDelete)
     */
    public function isDestructive(): bool
    {
        return in_array($this->action(), ['delete', 'forceDelete']);
    }
}
```

### **1.2 Template Enum**

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
            self::SUPER_ADMIN => array_map(fn($p) => $p->value, Permission::cases()),
            
            self::ADMIN => [
                Permission::USER_VIEW_ANY->value,
                Permission::USER_VIEW->value,
                Permission::USER_CREATE->value,
                Permission::USER_UPDATE->value,
                Permission::SHOP_VIEW_ANY->value,
                Permission::SHOP_VIEW->value,
                Permission::SHOP_CREATE->value,
                Permission::SHOP_UPDATE->value,
                Permission::KITCHEN_VIEW_ANY->value,
                Permission::KITCHEN_VIEW->value,
                Permission::AUDIT_VIEW_ANY->value,
            ],
            
            self::SHOP_MANAGER => [
                Permission::SHOP_VIEW->value,
                Permission::SHOP_UPDATE->value,
                Permission::SHOP_MANAGE_STAFF->value,
            ],
            
            self::KITCHEN_MANAGER => [
                Permission::KITCHEN_VIEW->value,
                Permission::KITCHEN_UPDATE->value,
                Permission::KITCHEN_MANAGE_STAFF->value,
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
     * Check if template can manage entity type
     */
    public function canManage(string $entity): bool
    {
        return match($this) {
            self::SUPER_ADMIN, self::ADMIN => true,
            self::SHOP_MANAGER => $entity === 'shop',
            self::KITCHEN_MANAGER => $entity === 'kitchen',
            self::SUPERVISOR => in_array($entity, ['shop', 'kitchen', 'driver']),
            default => false,
        };
    }

    /**
     * Get Filament panel this template can access
     */
    public function panel(): string
    {
        return match($this) {
            self::SUPER_ADMIN, self::ADMIN => 'admin',
            self::SHOP_MANAGER, self::SHOP_STAFF => 'shop',
            self::KITCHEN_MANAGER, self::KITCHEN_STAFF => 'kitchen',
            self::DRIVER => 'driver',
            self::SUPERVISOR => 'supervisor',
            self::SUPPLIER_MANAGER => 'supplier',
            default => 'customer',
        };
    }
}
```

### **1.3 EntityType Enum**

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

    /**
     * Get model class for entity
     */
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

    /**
     * Get plural label
     */
    public function plural(): string
    {
        return match($this) {
            self::USER => 'users',
            self::SHOP => 'shops',
            self::KITCHEN => 'kitchens',
            self::DRIVER => 'drivers',
            self::SUPERVISOR => 'supervisors',
            self::SUPPLIER => 'suppliers',
            self::PERMISSION => 'permissions',
            self::TEMPLATE => 'templates',
            self::DELEGATION => 'delegations',
            self::REQUEST => 'requests',
        };
    }
}
```

### **1.4 RequestStatus Enum**

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

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-ban',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::CANCELLED]);
    }

    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::PENDING => in_array($status, [self::APPROVED, self::REJECTED, self::CANCELLED]),
            self::APPROVED, self::REJECTED, self::CANCELLED => false,
        };
    }
}
```

---

## 📋 PHASE 2 : DATA OBJECTS AVEC VALIDATION (4h)

### **2.1 Permission DTOs**

**Fichier** : `app/Data/Permissions/PermissionData.php`

```php
<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

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
    public static function fromEnum(PermissionEnum $permission, int $groupId = 1): self
    {
        return new self(
            name: $permission->label(),
            slug: $permission->value,
            description: "Permission to {$permission->action()} {$permission->resource()}",
            permission_group_id: $groupId,
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
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class AssignPermissionData extends Data
{
    public function __construct(
        #[Required]
        public int $user_id,
        
        #[Required, WithCast(EnumCast::class, PermissionEnum::class)]
        public PermissionEnum|string $permission, // Accept enum or string for custom
        
        public ?int $scope_id = null,
        
        public ?Carbon $valid_from = null,
        
        public ?Carbon $valid_until = null,
        
        public string $source = 'direct',
        
        public ?string $reason = null,
    ) {}

    /**
     * Get permission slug
     */
    public function permissionSlug(): string
    {
        return $this->permission instanceof PermissionEnum 
            ? $this->permission->value 
            : $this->permission;
    }
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
        public PermissionEnum|string $permission,
        public ?int $scope_id = null,
        public array $context = [],
    ) {}

    public function permissionSlug(): string
    {
        return $this->permission instanceof PermissionEnum 
            ? $this->permission->value 
            : $this->permission;
    }
}
```

### **2.2 Template DTOs**

**Fichier** : `app/Data/Templates/TemplateData.php`

```php
<?php

namespace App\Data\Templates;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

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
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class AssignTemplateData extends Data
{
    public function __construct(
        public int $user_id,
        
        #[WithCast(EnumCast::class, TemplateEnum::class)]
        public TemplateEnum|string $template,
        
        public bool $auto_sync = true,
        
        public ?Carbon $valid_from = null,
        
        public ?Carbon $valid_until = null,
    ) {}

    public function templateSlug(): string
    {
        return $this->template instanceof TemplateEnum 
            ? $this->template->value 
            : $this->template;
    }
}
```

### **2.3 User DTOs**

**Fichier** : `app/Data/Users/UserData.php`

```php
<?php

namespace App\Data\Users;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,
        
        #[Required, Email]
        public string $email,
        
        #[WithCast(EnumCast::class, TemplateEnum::class)]
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
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

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

**Fichier** : `app/Data/Users/UserWithRelationsData.php`

```php
<?php

namespace App\Data\Users;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class UserWithRelationsData extends Data
{
    public function __construct(
        public UserData $user,
        public Collection $permissions,
        public Collection $templates,
        public ?Collection $shops = null,
        public ?Collection $kitchens = null,
    ) {}
}
```

### **2.4 Business Entity DTOs**

**Fichier** : `app/Data/Shops/ShopData.php`

```php
<?php

namespace App\Data\Shops;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ShopData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,
        
        #[Required, Max(255)]
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

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class KitchenData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,
        
        #[Required, Max(255)]
        public string $slug,
        
        public ?string $description = null,
        
        public ?string $address = null,
        
        public bool $is_active = true,
    ) {}
}
```

**Similar DTOs for** : Driver, Supervisor, Supplier

---

## 📋 PHASE 3 : ACTIONS AVEC IDEMPOTENCE (5h)

### **3.1 Permission Actions**

**Fichier** : `app/Actions/Permissions/AssignPermissionToUser.php`

```php
<?php

namespace App\Actions\Permissions;

use App\Data\Permissions\AssignPermissionData;
use App\Enums\AuditAction;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignPermissionToUser
{
    use AsAction;

    /**
     * Assign permission to user
     * 
     * @param AssignPermissionData $data
     * @param bool $skipIfExists Skip silently if already assigned
     * @return bool True if assigned, false if skipped
     */
    public function handle(AssignPermissionData $data, bool $skipIfExists = true): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $skipIfExists) {
                $user = User::findOrFail($data->user_id);
                $permission = Permission::where('slug', $data->permissionSlug())->firstOrFail();

                // Check if already assigned (idempotence)
                $exists = $user->permissions()
                    ->where('permission_id', $permission->id)
                    ->where('scope_id', $data->scope_id)
                    ->exists();

                if ($exists) {
                    if ($skipIfExists) {
                        return false; // Silently skip
                    }
                    throw new \RuntimeException('Permission already assigned');
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

                // Enriched activity log
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'permission' => $data->permissionSlug(),
                        'permission_name' => $permission->name,
                        'scope_id' => $data->scope_id,
                        'source' => $data->source,
                        'reason' => $data->reason,
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log(AuditAction::GRANTED->value);

                // Clear cache
                Cache::tags(['users', "user.{$user->id}", 'permissions'])->flush();

                return true;
            });
        } finally {
            // Metrics logging
            $duration = (microtime(true) - $startTime) * 1000;
            Log::channel('metrics')->info('permission.assigned', [
                'user_id' => $data->user_id,
                'permission' => $data->permissionSlug(),
                'duration_ms' => round($duration, 2),
            ]);
        }
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
        PermissionEnum|string $permission,
        ?int $scopeId = null,
        ?string $reason = null
    ): bool {
        return DB::transaction(function () use ($userId, $permission, $scopeId, $reason) {
            $user = User::findOrFail($userId);
            
            $permissionSlug = $permission instanceof PermissionEnum 
                ? $permission->value 
                : $permission;
            
            $permissionModel = Permission::where('slug', $permissionSlug)->firstOrFail();

            $detached = $user->permissions()
                ->wherePivot('permission_id', $permissionModel->id)
                ->wherePivot('scope_id', $scopeId)
                ->detach();

            if ($detached) {
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'permission' => $permissionSlug,
                        'scope_id' => $scopeId,
                        'reason' => $reason,
                        'ip' => request()->ip(),
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
            permission: $data->permissionSlug(),
            scopeId: $data->scope_id,
            context: $data->context
        );
    }
}
```

### **3.2 Template Actions**

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

    public function handle(AssignTemplateData $data, bool $skipIfExists = true): bool
    {
        return DB::transaction(function () use ($data, $skipIfExists) {
            $user = User::findOrFail($data->user_id);
            $template = PermissionTemplate::where('slug', $data->templateSlug())
                ->firstOrFail();

            // Check if already assigned
            if ($user->templates()->where('id', $template->id)->exists()) {
                if ($skipIfExists) {
                    return false;
                }
                throw new \RuntimeException('Template already assigned');
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

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'template' => $data->templateSlug(),
                    'template_name' => $template->name,
                    'ip' => request()->ip(),
                ])
                ->log('template_assigned');

            Cache::tags(['users', "user.{$user->id}", 'templates'])->flush();

            return true;
        });
    }
}
```

**Similar for** : RevokeTemplateFromUser, SetPrimaryTemplate

### **3.3 CRUD Actions Examples**

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
                ->withProperties(['ip' => request()->ip()])
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

## 📋 PHASE 4 : POLICIES AVEC CACHE (4h)

### **4.1 ChecksPermissions Trait**

**Fichier** : `app/Policies/Concerns/ChecksPermissions.php`

```php
<?php

namespace App\Policies\Concerns;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Facades\Cache;

trait ChecksPermissions
{
    /**
     * Cached permissions for current request
     */
    protected array $cachedPermissions = [];

    /**
     * Check if user has permission (with request-level cache)
     */
    protected function can(
        User $user,
        PermissionEnum|string $permission,
        ?int $scopeId = null
    ): bool {
        $permissionSlug = $permission instanceof PermissionEnum 
            ? $permission->value 
            : $permission;

        $cacheKey = "policy.{$user->id}.{$permissionSlug}.{$scopeId}";

        // Request-level cache
        if (isset($this->cachedPermissions[$cacheKey])) {
            return $this->cachedPermissions[$cacheKey];
        }

        $result = app(PermissionChecker::class)->userHasPermission(
            userId: $user->id,
            permission: $permissionSlug,
            scopeId: $scopeId
        );

        $this->cachedPermissions[$cacheKey] = $result;

        return $result;
    }

    /**
     * Check if user has any of the permissions
     */
    protected function canAny(
        User $user,
        array $permissions,
        ?int $scopeId = null
    ): bool {
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
    protected function canAll(
        User $user,
        array $permissions,
        ?int $scopeId = null
    ): bool {
        foreach ($permissions as $permission) {
            if (!$this->can($user, $permission, $scopeId)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Preload permissions for batch operations
     */
    protected function preloadPermissions(User $user, array $permissions): void
    {
        $checker = app(PermissionChecker::class);
        
        foreach ($permissions as $permission) {
            $permissionSlug = $permission instanceof PermissionEnum 
                ? $permission->value 
                : $permission;
            
            $cacheKey = "policy.{$user->id}.{$permissionSlug}.null";
            
            $this->cachedPermissions[$cacheKey] = $checker->userHasPermission(
                userId: $user->id,
                permission: $permissionSlug
            );
        }
    }
}
```

### **4.2 Example Policies**

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

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::USER_VIEW_ANY);
    }

    public function view(User $user, User $model): bool
    {
        // Can view self
        if ($user->id === $model->id) {
            return true;
        }

        return $this->can($user, Permission::USER_VIEW);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::USER_CREATE);
    }

    public function update(User $user, User $model): bool
    {
        // Cannot update self via this permission (separate permission needed)
        if ($user->id === $model->id) {
            return false;
        }

        return $this->can($user, Permission::USER_UPDATE);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $this->can($user, Permission::USER_DELETE);
    }

    public function restore(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_RESTORE);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_FORCE_DELETE);
    }

    public function assignTemplate(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_ASSIGN_TEMPLATE);
    }

    public function assignPermission(User $user, User $model): bool
    {
        return $this->can($user, Permission::USER_ASSIGN_PERMISSION);
    }
}
```

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
        // Check global permission OR scoped permission
        return $this->can($user, Permission::SHOP_VIEW)
            || $this->can($user, Permission::SHOP_VIEW, $shop->id);
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

    public function restore(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_RESTORE);
    }

    public function forceDelete(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_FORCE_DELETE);
    }

    public function manageStaff(User $user, Shop $shop): bool
    {
        return $this->can($user, Permission::SHOP_MANAGE_STAFF, $shop->id);
    }
}
```

**Create similar policies for** : Kitchen, Driver, Supervisor, Supplier, Permission, Template

### **4.3 Register Policies**

**Fichier** : `app/Providers/AuthServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Models\Driver;
use App\Models\Kitchen;
use App\Models\Permission;
use App\Models\PermissionTemplate;
use App\Models\Shop;
use App\Models\Supervisor;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\DriverPolicy;
use App\Policies\KitchenPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ShopPolicy;
use App\Policies\SupervisorPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\TemplatePolicy;
use App\Policies\UserPolicy;
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

    public function boot(): void
    {
        //
    }
}
```

---

## 📋 PHASE 5 : MIDDLEWARE & FILAMENT (3h)

### **5.1 CheckPermission Middleware**

**Fichier** : `app/Http/Middleware/CheckPermission.php`

```php
<?php

namespace App\Http\Middleware;

use App\Enums\Permission as PermissionEnum;
use App\Services\Permissions\PermissionChecker;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function __construct(
        private PermissionChecker $checker
    ) {}

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $scopeParam = null)
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Try to get permission from enum
        $permissionEnum = PermissionEnum::tryFrom($permission);
        $permissionSlug = $permissionEnum?->value ?? $permission;

        // Get scope from route parameter if specified
        $scopeId = $scopeParam ? $request->route($scopeParam) : null;

        if (!$this->checker->userHasPermission($user->id, $permissionSlug, $scopeId)) {
            abort(403, 'Unauthorized action');
        }

        return $next($request);
    }
}
```

**Register in** `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
    ]);
})
```

**Usage in routes**:

```php
use App\Enums\Permission;

Route::post('/shops', CreateShop::class)
    ->middleware('permission:' . Permission::SHOP_CREATE->value);

Route::put('/shops/{shop}', UpdateShop::class)
    ->middleware('permission:' . Permission::SHOP_UPDATE->value . ',shop');
```

### **5.2 Filament Base Resource**

**Fichier** : `app/Filament/Resources/BaseResource.php`

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

abstract class BaseResource extends Resource
{
    /**
     * Check if current user can view any
     */
    public static function canViewAny(): bool
    {
        return static::can('viewAny');
    }

    /**
     * Check if current user can create
     */
    public static function canCreate(): bool
    {
        return static::can('create');
    }

    /**
     * Check if current user can edit
     */
    public static function canEdit($record): bool
    {
        return static::can('update', $record);
    }

    /**
     * Check if current user can delete
     */
    public static function canDelete($record): bool
    {
        return static::can('delete', $record);
    }

    /**
     * Check permission via policy
     */
    protected static function can(string $ability, $record = null): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $user->can($ability, $record ?? static::getModel());
    }
}
```

**Update existing resources to extend BaseResource**:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BaseResource; // Change this
use App\Filament\Resources\ShopResource\Pages;
use App\Models\Shop;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables;

class ShopResource extends BaseResource // Change this
{
    protected static ?string $model = Shop::class;

    // ... rest of resource
    // Authorization methods are now inherited from BaseResource
}
```

---

## 📋 PHASE 6 : SERVICES UPDATE (2h)

### **6.1 Update PermissionChecker**

**Fichier** : `app/Services/Permissions/PermissionChecker.php`

```php
<?php

namespace App\Services\Permissions;

use App\Enums\Permission as PermissionEnum;
use Illuminate\Support\Facades\Cache;

class PermissionChecker
{
    /**
     * Check if user has permission (accepts enum or string)
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

    /**
     * Get all permissions for user (for batch operations)
     */
    public function getAllPermissions(int $userId): array
    {
        return Cache::tags(['permissions', "user.{$userId}"])
            ->remember(
                "all_permissions.{$userId}",
                3600,
                fn() => $this->fetchAllPermissions($userId)
            );
    }

    /**
     * Internal check logic
     */
    protected function checkPermission(
        int $userId,
        string $permissionSlug,
        ?int $scopeId,
        array $context
    ): bool {
        // Existing implementation...
        // Use ConditionEvaluator, WildcardExpander, etc.
    }

    /**
     * Fetch all permissions for user
     */
    protected function fetchAllPermissions(int $userId): array
    {
        // Implementation to fetch all user permissions
    }
}
```

---

## 📋 PHASE 7 : COMMANDES ARTISAN (3h)

### **7.1 Generate Permissions from Enum**

**Fichier** : `app/Console/Commands/GeneratePermissionsFromEnum.php`

```php
<?php

namespace App\Console\Commands;

use App\Data\Permissions\PermissionData;
use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Console\Command;

class GeneratePermissionsFromEnum extends Command
{
    protected $signature = 'permissions:generate-from-enum {--dry-run}';
    protected $description = 'Generate permissions in database from Permission enum';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        // Get or create default group
        $defaultGroup = PermissionGroup::firstOrCreate(
            ['slug' => 'system'],
            ['name' => 'System Permissions', 'description' => 'Auto-generated system permissions']
        );

        foreach (PermissionEnum::cases() as $permission) {
            $exists = Permission::where('slug', $permission->value)->exists();

            if ($exists) {
                $this->info("⏭️  Skipped: {$permission->value} (already exists)");
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                $data = PermissionData::fromEnum($permission, $defaultGroup->id);
                Permission::create($data->toArray());
            }

            $this->info("✅ Created: {$permission->value}");
            $created++;
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("   Created: {$created}");
        $this->info("   Skipped: {$skipped}");
        
        if ($dryRun) {
            $this->warn("   ⚠️  Dry run mode - no changes made");
        }
    }
}
```

### **7.2 Sync Enum to Database**

**Fichier** : `app/Console/Commands/SyncPermissions.php`

```php
<?php

namespace App\Console\Commands;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use Illuminate\Console\Command;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync {--prune}';
    protected $description = 'Sync permissions between enum and database';

    public function handle()
    {
        $prune = $this->option('prune');

        // Add missing permissions
        $this->call('permissions:generate-from-enum');

        if ($prune) {
            $this->info('🗑️  Pruning permissions not in enum...');
            
            $enumValues = array_map(fn($p) => $p->value, PermissionEnum::cases());
            $deleted = Permission::whereNotIn('slug', $enumValues)
                ->where('is_system', true)
                ->delete();

            $this->info("   Deleted: {$deleted}");
        }

        $this->info('✅ Sync complete!');
    }
}
```

### **7.3 Check Policies**

**Fichier** : `app/Console/Commands/CheckPolicies.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;

class CheckPolicies extends Command
{
    protected $signature = 'policies:check';
    protected $description = 'Check which models have policies registered';

    public function handle()
    {
        $policies = Gate::policies();

        $this->info('📋 Registered Policies:');
        $this->newLine();

        foreach ($policies as $model => $policy) {
            $this->info("   {$model} → {$policy}");
        }

        $this->newLine();
        $this->info("Total: " . count($policies));
    }
}
```

---

## 📋 PHASE 8 : TESTS (4h)

### **8.1 Action Tests Example**

**Fichier** : `tests/Feature/Actions/AssignPermissionToUserTest.php`

```php
<?php

use App\Actions\Permissions\AssignPermissionToUser;
use App\Data\Permissions\AssignPermissionData;
use App\Enums\Permission;
use App\Models\Permission as PermissionModel;
use App\Models\User;

it('assigns permission to user', function () {
    $user = User::factory()->create();
    $permission = PermissionModel::factory()->create(['slug' => Permission::USER_VIEW->value]);

    $data = new AssignPermissionData(
        user_id: $user->id,
        permission: Permission::USER_VIEW,
    );

    $result = AssignPermissionToUser::run($data);

    expect($result)->toBeTrue();
    expect($user->fresh()->permissions)->toHaveCount(1);
});

it('is idempotent when skipIfExists is true', function () {
    $user = User::factory()->create();
    $permission = PermissionModel::factory()->create(['slug' => Permission::USER_VIEW->value]);

    $data = new AssignPermissionData(
        user_id: $user->id,
        permission: Permission::USER_VIEW,
    );

    $result1 = AssignPermissionToUser::run($data);
    $result2 = AssignPermissionToUser::run($data, skipIfExists: true);

    expect($result1)->toBeTrue();
    expect($result2)->toBeFalse();
    expect($user->fresh()->permissions)->toHaveCount(1);
});
```

### **8.2 Policy Tests Example**

**Fichier** : `tests/Feature/Policies/UserPolicyTest.php`

```php
<?php

use App\Enums\Permission;
use App\Models\Permission as PermissionModel;
use App\Models\User;

it('allows user with permission to view any users', function () {
    $user = User::factory()->create();
    $permission = PermissionModel::factory()->create(['slug' => Permission::USER_VIEW_ANY->value]);
    $user->permissions()->attach($permission->id);

    expect($user->can('viewAny', User::class))->toBeTrue();
});

it('denies user without permission', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', User::class))->toBeFalse();
});
```

---

## ✅ CHECKLIST COMPLÈTE

### **Phase 1 : Enums (3h)**
- [ ] `Permission` enum avec 100+ cases + helper methods
- [ ] `Template` enum avec labels et permissions par défaut
- [ ] `EntityType` enum avec modelClass()
- [ ] `RequestStatus` enum avec transitions

### **Phase 2 : Data Objects (4h)**
- [ ] Permission DTOs (PermissionData, AssignPermissionData, PermissionCheckData)
- [ ] Template DTOs (TemplateData, AssignTemplateData)
- [ ] User DTOs (UserData, UserRegistrationData, UserWithRelationsData)
- [ ] Business DTOs (Shop, Kitchen, Driver, Supervisor, Supplier)

### **Phase 3 : Actions (5h)**
- [ ] Permission actions (Assign, Revoke, Check) avec idempotence
- [ ] Template actions (Assign, Revoke, SetPrimary)
- [ ] CRUD actions (Create, Update, Delete, Restore) pour Shop
- [ ] Similar CRUD pour Kitchen, Driver, Supervisor, Supplier

### **Phase 4 : Policies (4h)**
- [ ] `ChecksPermissions` trait avec cache request-level
- [ ] UserPolicy avec toutes méthodes
- [ ] ShopPolicy, KitchenPolicy
- [ ] DriverPolicy, SupervisorPolicy, SupplierPolicy
- [ ] PermissionPolicy, TemplatePolicy
- [ ] Register dans AuthServiceProvider

### **Phase 5 : Middleware & Filament (3h)**
- [ ] `CheckPermission` middleware
- [ ] Register middleware alias
- [ ] `BaseResource` pour Filament
- [ ] Update existing resources to extend BaseResource

### **Phase 6 : Services (2h)**
- [ ] Update PermissionChecker pour accepter enums
- [ ] Ajouter getAllPermissions() pour batch
- [ ] Test avec enums

### **Phase 7 : Commandes (3h)**
- [ ] `permissions:generate-from-enum`
- [ ] `permissions:sync --prune`
- [ ] `policies:check`

### **Phase 8 : Tests (4h)**
- [ ] Tests Actions (Assign, Revoke avec idempotence)
- [ ] Tests Policies (viewAny, view, create, update, delete)
- [ ] Tests DTOs validation
- [ ] `./vendor/bin/phpstan analyse` validation

---

## 🎯 RÉSULTAT FINAL

```
app/
├── Actions/
│   ├── Permissions/ (Assign, Revoke, Check)
│   ├── Templates/ (Assign, Revoke, SetPrimary)
│   └── Shops/ (Create, Update, Delete, Restore)
│
├── Data/
│   ├── Permissions/ (PermissionData, AssignPermissionData, PermissionCheckData)
│   ├── Templates/ (TemplateData, AssignTemplateData)
│   └── Users/ (UserData, UserRegistrationData, UserWithRelationsData)
│
├── Enums/
│   ├── Permission.php (100+ permissions type-safe)
│   ├── Template.php
│   ├── EntityType.php
│   └── RequestStatus.php
│
├── Http/Middleware/
│   └── CheckPermission.php
│
├── Policies/
│   ├── Concerns/ChecksPermissions.php (avec cache)
│   ├── UserPolicy.php
│   └── ShopPolicy.php (+ Kitchen, Driver, etc.)
│
├── Console/Commands/
│   ├── GeneratePermissionsFromEnum.php
│   ├── SyncPermissions.php
│   └── CheckPolicies.php
│
└── Filament/Resources/
    └── BaseResource.php
```

---

## 💡 UTILISATION FINALE

```php
// ✅ Type-safe Actions
AssignPermissionToUser::run(
    AssignPermissionData::from([
        'user_id' => $user->id,
        'permission' => Permission::SHOP_UPDATE,
        'scope_id' => $shop->id,
    ])
);

// ✅ Policies
$this->authorize('update', $shop);

// ✅ Middleware
Route::post('/shops', CreateShop::class)
    ->middleware('permission:' . Permission::SHOP_CREATE->value);

// ✅ Filament (auto)
class ShopResource extends BaseResource {
    // canViewAny(), canCreate(), etc. auto-héritées
}
```

---

**Architecture production-ready, type-safe, performante ! 🚀**

**Commence l'implémentation et tiens-moi au courant à chaque phase !**
