# 🚀 PROMPT CLAUDE CODE - PARTIE 4 : ENUMS

> **Contexte** : Créer enums type-safe pour actions, wildcards, audit, et conditions

---

## 📋 OBJECTIF

Créer **4 fichiers Enums** pour ajouter type-safety et métadonnées à l'architecture de permissions.

**Principe** : Enums PHP 8.1+ avec méthodes helper et métadonnées riches.

---

## 🎯 CONTRAINTES STRICTES

### **Type-Safety**
- ✅ Utiliser Enums PHP 8.1+ (backed enums)
- ✅ Type hints partout
- ✅ Return types explicites
- ✅ PHPDoc complet

### **Métadonnées Riches**
- ✅ Description pour chaque case
- ✅ Icon (Heroicon) pour UI
- ✅ Color pour badges Filament
- ✅ Groupement logique (helpers)

### **Code Quality**
- ✅ PHPDoc exhaustif avec exemples
- ✅ Méthodes statiques pour groups
- ✅ Méthodes d'instance pour metadata
- ✅ < 200 lignes par fichier

---

## 📁 LISTE DES 4 ENUMS À CRÉER

```
app/Enums/PermissionAction.php
app/Enums/WildcardPattern.php
app/Enums/AuditAction.php
app/Enums/ConditionType.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **ENUM 1 : PermissionAction**

**Fichier** : `app/Enums/PermissionAction.php`

**Purpose** : Actions CRUD + management sur ressources

**Cases** :
```php
enum PermissionAction: string
{
    // CRUD standard
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
    
    // Actions lecture étendues
    case VIEW = 'view';
    case LIST = 'list';
    
    // Actions export/import
    case EXPORT = 'export';
    case IMPORT = 'import';
    
    // Actions archivage
    case ARCHIVE = 'archive';
    case RESTORE = 'restore';
    
    // Actions management
    case MANAGE = 'manage';
    case ADMIN = 'admin';
}
```

**Méthodes Statiques (Groupes)** :
```php
/**
 * Get all write actions (create, update, delete)
 *
 * @return array<string>
 */
public static function writeActions(): array
{
    return [
        self::CREATE->value,
        self::UPDATE->value,
        self::DELETE->value,
    ];
}

/**
 * Get all read actions (read, view, list)
 *
 * @return array<string>
 */
public static function readActions(): array
{
    return [
        self::READ->value,
        self::VIEW->value,
        self::LIST->value,
    ];
}

/**
 * Get all admin actions (full CRUD + export + import + manage + admin)
 *
 * @return array<string>
 */
public static function adminActions(): array
{
    return [
        self::CREATE->value,
        self::READ->value,
        self::UPDATE->value,
        self::DELETE->value,
        self::EXPORT->value,
        self::IMPORT->value,
        self::MANAGE->value,
        self::ADMIN->value,
    ];
}

/**
 * Get all management actions (admin, manage, archive, restore)
 *
 * @return array<string>
 */
public static function managementActions(): array
{
    return [
        self::MANAGE->value,
        self::ADMIN->value,
        self::ARCHIVE->value,
        self::RESTORE->value,
    ];
}
```

**Méthodes d'Instance (Metadata)** :
```php
/**
 * Check if action is a write action
 */
public function isWrite(): bool
{
    return in_array($this->value, self::writeActions());
}

/**
 * Check if action is a read action
 */
public function isRead(): bool
{
    return in_array($this->value, self::readActions());
}

/**
 * Get action description
 */
public function description(): string
{
    return match($this) {
        self::CREATE => 'Create new resources',
        self::READ => 'Read resource details',
        self::UPDATE => 'Update existing resources',
        self::DELETE => 'Delete resources permanently',
        self::VIEW => 'View resource in detail',
        self::LIST => 'List all resources',
        self::EXPORT => 'Export resources to file',
        self::IMPORT => 'Import resources from file',
        self::ARCHIVE => 'Archive resources (soft delete)',
        self::RESTORE => 'Restore archived resources',
        self::MANAGE => 'Full management access',
        self::ADMIN => 'Full administrative access',
    };
}

/**
 * Get Heroicon for action
 */
public function icon(): string
{
    return match($this) {
        self::CREATE => 'heroicon-o-plus-circle',
        self::READ => 'heroicon-o-eye',
        self::UPDATE => 'heroicon-o-pencil',
        self::DELETE => 'heroicon-o-trash',
        self::VIEW => 'heroicon-o-document-magnifying-glass',
        self::LIST => 'heroicon-o-list-bullet',
        self::EXPORT => 'heroicon-o-arrow-down-tray',
        self::IMPORT => 'heroicon-o-arrow-up-tray',
        self::ARCHIVE => 'heroicon-o-archive-box',
        self::RESTORE => 'heroicon-o-arrow-uturn-left',
        self::MANAGE => 'heroicon-o-cog-6-tooth',
        self::ADMIN => 'heroicon-o-shield-check',
    };
}

/**
 * Get color for Filament badge
 */
public function color(): string
{
    return match($this) {
        self::CREATE => 'success',
        self::READ, self::VIEW, self::LIST => 'info',
        self::UPDATE => 'warning',
        self::DELETE => 'danger',
        self::EXPORT, self::IMPORT => 'gray',
        self::ARCHIVE => 'warning',
        self::RESTORE => 'success',
        self::MANAGE, self::ADMIN => 'primary',
    };
}
```

---

### **ENUM 2 : WildcardPattern**

**Fichier** : `app/Enums/WildcardPattern.php`

**Purpose** : Patterns de wildcards prédéfinis pour expansion automatique

**Cases** :
```php
enum WildcardPattern: string
{
    // Global wildcards
    case ALL = '*.*';
    case ALL_READ = '*.read';
    case ALL_WRITE = '*.write';
    case ALL_ADMIN = '*.admin';
    
    // Shops wildcards
    case SHOPS_ALL = 'shops.*';
    case SHOPS_READ = 'shops.read';
    case SHOPS_WRITE = 'shops.write';
    case SHOPS_ADMIN = 'shops.admin';
    
    // Users wildcards
    case USERS_ALL = 'users.*';
    case USERS_READ = 'users.read';
    case USERS_WRITE = 'users.write';
    case USERS_ADMIN = 'users.admin';
    
    // Products wildcards
    case PRODUCTS_ALL = 'products.*';
    case PRODUCTS_READ = 'products.read';
    case PRODUCTS_WRITE = 'products.write';
    
    // Orders wildcards
    case ORDERS_ALL = 'orders.*';
    case ORDERS_READ = 'orders.read';
    case ORDERS_WRITE = 'orders.write';
    
    // Settings wildcards
    case SETTINGS_ALL = 'settings.*';
    case SETTINGS_READ = 'settings.read';
    case SETTINGS_WRITE = 'settings.write';
}
```

**Méthodes Statiques (Groupes)** :
```php
/**
 * Get all global patterns (*.*. *.read, etc.)
 *
 * @return array<WildcardPattern>
 */
public static function globalPatterns(): array
{
    return [
        self::ALL,
        self::ALL_READ,
        self::ALL_WRITE,
        self::ALL_ADMIN,
    ];
}

/**
 * Get all shop patterns
 *
 * @return array<WildcardPattern>
 */
public static function shopPatterns(): array
{
    return [
        self::SHOPS_ALL,
        self::SHOPS_READ,
        self::SHOPS_WRITE,
        self::SHOPS_ADMIN,
    ];
}

/**
 * Get all user patterns
 *
 * @return array<WildcardPattern>
 */
public static function userPatterns(): array
{
    return [
        self::USERS_ALL,
        self::USERS_READ,
        self::USERS_WRITE,
        self::USERS_ADMIN,
    ];
}

/**
 * Get patterns by resource type
 *
 * @param string $resource Resource name (shops, users, products, etc.)
 * @return array<WildcardPattern>
 */
public static function forResource(string $resource): array
{
    return match($resource) {
        'shops' => self::shopPatterns(),
        'users' => self::userPatterns(),
        'products' => [self::PRODUCTS_ALL, self::PRODUCTS_READ, self::PRODUCTS_WRITE],
        'orders' => [self::ORDERS_ALL, self::ORDERS_READ, self::ORDERS_WRITE],
        'settings' => [self::SETTINGS_ALL, self::SETTINGS_READ, self::SETTINGS_WRITE],
        default => [],
    };
}
```

**Méthodes d'Instance (Metadata)** :
```php
/**
 * Get pattern description
 */
public function description(): string
{
    return match($this) {
        self::ALL => 'All permissions on all resources',
        self::ALL_READ => 'Read permissions on all resources',
        self::ALL_WRITE => 'Write permissions (create, update, delete) on all resources',
        self::ALL_ADMIN => 'Full admin permissions on all resources',
        
        self::SHOPS_ALL => 'All permissions on shops',
        self::SHOPS_READ => 'Read shop details',
        self::SHOPS_WRITE => 'Create, update, delete shops',
        self::SHOPS_ADMIN => 'Full admin access to shops',
        
        self::USERS_ALL => 'All permissions on users',
        self::USERS_READ => 'Read user details',
        self::USERS_WRITE => 'Create, update, delete users',
        self::USERS_ADMIN => 'Full admin access to users',
        
        self::PRODUCTS_ALL => 'All permissions on products',
        self::PRODUCTS_READ => 'Read product details',
        self::PRODUCTS_WRITE => 'Create, update, delete products',
        
        self::ORDERS_ALL => 'All permissions on orders',
        self::ORDERS_READ => 'Read order details',
        self::ORDERS_WRITE => 'Create, update, delete orders',
        
        self::SETTINGS_ALL => 'All permissions on settings',
        self::SETTINGS_READ => 'Read settings',
        self::SETTINGS_WRITE => 'Update settings',
    };
}

/**
 * Get Heroicon for pattern
 */
public function icon(): string
{
    return match($this) {
        self::ALL, self::ALL_READ, self::ALL_WRITE, self::ALL_ADMIN => 'heroicon-o-globe-alt',
        
        self::SHOPS_ALL, self::SHOPS_READ, self::SHOPS_WRITE, self::SHOPS_ADMIN => 'heroicon-o-building-storefront',
        
        self::USERS_ALL, self::USERS_READ, self::USERS_WRITE, self::USERS_ADMIN => 'heroicon-o-users',
        
        self::PRODUCTS_ALL, self::PRODUCTS_READ, self::PRODUCTS_WRITE => 'heroicon-o-cube',
        
        self::ORDERS_ALL, self::ORDERS_READ, self::ORDERS_WRITE => 'heroicon-o-shopping-cart',
        
        self::SETTINGS_ALL, self::SETTINGS_READ, self::SETTINGS_WRITE => 'heroicon-o-cog-6-tooth',
    };
}

/**
 * Get color for Filament badge
 */
public function color(): string
{
    return match($this) {
        self::ALL, self::ALL_ADMIN => 'danger',
        self::ALL_READ => 'info',
        self::ALL_WRITE => 'warning',
        
        self::SHOPS_ADMIN, self::USERS_ADMIN => 'primary',
        self::SHOPS_WRITE, self::USERS_WRITE, self::PRODUCTS_WRITE, self::ORDERS_WRITE, self::SETTINGS_WRITE => 'warning',
        self::SHOPS_READ, self::USERS_READ, self::PRODUCTS_READ, self::ORDERS_READ, self::SETTINGS_READ => 'info',
        
        default => 'gray',
    };
}

/**
 * Get pattern type (full, resource, action, macro)
 */
public function patternType(): string
{
    if ($this->value === '*.*') {
        return 'full';
    }
    
    if (str_ends_with($this->value, '.*')) {
        return 'resource';
    }
    
    if (str_starts_with($this->value, '*.')) {
        return 'action';
    }
    
    return 'macro';
}
```

---

### **ENUM 3 : AuditAction**

**Fichier** : `app/Enums/AuditAction.php`

**Purpose** : Actions auditées dans permission_audit_log

**Cases** :
```php
enum AuditAction: string
{
    // Permission lifecycle
    case GRANTED = 'granted';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';
    case UPDATED = 'updated';
    
    // Inheritance sources
    case INHERITED = 'inherited';
    case INHERITED_REMOVED = 'inherited_removed';
    
    // Template operations
    case TEMPLATE_ASSIGNED = 'template_assigned';
    case TEMPLATE_REMOVED = 'template_removed';
    case TEMPLATE_SYNCED = 'template_synced';
    
    // Delegation operations
    case DELEGATED = 'delegated';
    case DELEGATION_REVOKED = 'delegation_revoked';
    case DELEGATION_EXPIRED = 'delegation_expired';
    
    // Request workflow
    case REQUESTED = 'requested';
    case REQUEST_APPROVED = 'request_approved';
    case REQUEST_REJECTED = 'request_rejected';
}
```

**Méthodes Statiques (Groupes)** :
```php
/**
 * Get all permission lifecycle actions
 *
 * @return array<string>
 */
public static function lifecycleActions(): array
{
    return [
        self::GRANTED->value,
        self::REVOKED->value,
        self::EXPIRED->value,
        self::UPDATED->value,
    ];
}

/**
 * Get all template-related actions
 *
 * @return array<string>
 */
public static function templateActions(): array
{
    return [
        self::TEMPLATE_ASSIGNED->value,
        self::TEMPLATE_REMOVED->value,
        self::TEMPLATE_SYNCED->value,
    ];
}

/**
 * Get all delegation-related actions
 *
 * @return array<string>
 */
public static function delegationActions(): array
{
    return [
        self::DELEGATED->value,
        self::DELEGATION_REVOKED->value,
        self::DELEGATION_EXPIRED->value,
    ];
}

/**
 * Get all request workflow actions
 *
 * @return array<string>
 */
public static function requestActions(): array
{
    return [
        self::REQUESTED->value,
        self::REQUEST_APPROVED->value,
        self::REQUEST_REJECTED->value,
    ];
}
```

**Méthodes d'Instance (Metadata)** :
```php
/**
 * Get action description
 */
public function description(): string
{
    return match($this) {
        self::GRANTED => 'Permission granted to user',
        self::REVOKED => 'Permission revoked from user',
        self::EXPIRED => 'Permission expired automatically',
        self::UPDATED => 'Permission updated',
        
        self::INHERITED => 'Permission inherited from parent',
        self::INHERITED_REMOVED => 'Inherited permission removed',
        
        self::TEMPLATE_ASSIGNED => 'Template assigned to user',
        self::TEMPLATE_REMOVED => 'Template removed from user',
        self::TEMPLATE_SYNCED => 'Permissions synced from template',
        
        self::DELEGATED => 'Permission delegated to user',
        self::DELEGATION_REVOKED => 'Delegation revoked',
        self::DELEGATION_EXPIRED => 'Delegation expired',
        
        self::REQUESTED => 'Permission requested by user',
        self::REQUEST_APPROVED => 'Permission request approved',
        self::REQUEST_REJECTED => 'Permission request rejected',
    };
}

/**
 * Get Heroicon for action
 */
public function icon(): string
{
    return match($this) {
        self::GRANTED => 'heroicon-o-check-circle',
        self::REVOKED => 'heroicon-o-x-circle',
        self::EXPIRED => 'heroicon-o-clock',
        self::UPDATED => 'heroicon-o-pencil',
        
        self::INHERITED => 'heroicon-o-arrow-down',
        self::INHERITED_REMOVED => 'heroicon-o-arrow-up',
        
        self::TEMPLATE_ASSIGNED => 'heroicon-o-clipboard-document-check',
        self::TEMPLATE_REMOVED => 'heroicon-o-clipboard-document-list',
        self::TEMPLATE_SYNCED => 'heroicon-o-arrow-path',
        
        self::DELEGATED => 'heroicon-o-user-plus',
        self::DELEGATION_REVOKED => 'heroicon-o-user-minus',
        self::DELEGATION_EXPIRED => 'heroicon-o-clock',
        
        self::REQUESTED => 'heroicon-o-hand-raised',
        self::REQUEST_APPROVED => 'heroicon-o-check-badge',
        self::REQUEST_REJECTED => 'heroicon-o-x-circle',
    };
}

/**
 * Get color for Filament badge
 */
public function color(): string
{
    return match($this) {
        self::GRANTED, self::TEMPLATE_ASSIGNED, self::REQUEST_APPROVED => 'success',
        self::REVOKED, self::TEMPLATE_REMOVED, self::DELEGATION_REVOKED, self::REQUEST_REJECTED => 'danger',
        self::EXPIRED, self::DELEGATION_EXPIRED => 'warning',
        self::UPDATED, self::TEMPLATE_SYNCED => 'info',
        self::INHERITED, self::INHERITED_REMOVED => 'gray',
        self::DELEGATED => 'primary',
        self::REQUESTED => 'warning',
    };
}

/**
 * Check if action is positive (grant/approve)
 */
public function isPositive(): bool
{
    return in_array($this, [
        self::GRANTED,
        self::TEMPLATE_ASSIGNED,
        self::DELEGATED,
        self::REQUEST_APPROVED,
    ]);
}

/**
 * Check if action is negative (revoke/reject)
 */
public function isNegative(): bool
{
    return in_array($this, [
        self::REVOKED,
        self::TEMPLATE_REMOVED,
        self::DELEGATION_REVOKED,
        self::REQUEST_REJECTED,
    ]);
}
```

---

### **ENUM 4 : ConditionType**

**Fichier** : `app/Enums/ConditionType.php`

**Purpose** : Types de conditions pour permissions contextuelles

**Cases** :
```php
enum ConditionType: string
{
    // Time-based conditions
    case TIME_RANGE = 'time_range';
    case DAYS = 'days';
    case DATE_RANGE = 'date_range';
    
    // Network conditions
    case IP_WHITELIST = 'ip_whitelist';
    case IP_BLACKLIST = 'ip_blacklist';
    
    // Security conditions
    case REQUIRES_2FA = 'requires_2fa';
    case REQUIRES_EMAIL_VERIFIED = 'requires_email_verified';
    
    // Business conditions
    case MAX_AMOUNT = 'max_amount';
    case MIN_AMOUNT = 'min_amount';
    
    // User attribute conditions
    case USER_ATTRIBUTES = 'user_attributes';
    
    // Custom conditions
    case CUSTOM = 'custom';
}
```

**Méthodes Statiques (Groupes)** :
```php
/**
 * Get all time-based condition types
 *
 * @return array<string>
 */
public static function timeConditions(): array
{
    return [
        self::TIME_RANGE->value,
        self::DAYS->value,
        self::DATE_RANGE->value,
    ];
}

/**
 * Get all network-based condition types
 *
 * @return array<string>
 */
public static function networkConditions(): array
{
    return [
        self::IP_WHITELIST->value,
        self::IP_BLACKLIST->value,
    ];
}

/**
 * Get all security condition types
 *
 * @return array<string>
 */
public static function securityConditions(): array
{
    return [
        self::REQUIRES_2FA->value,
        self::REQUIRES_EMAIL_VERIFIED->value,
    ];
}

/**
 * Get all business condition types
 *
 * @return array<string>
 */
public static function businessConditions(): array
{
    return [
        self::MAX_AMOUNT->value,
        self::MIN_AMOUNT->value,
    ];
}
```

**Méthodes d'Instance (Metadata)** :
```php
/**
 * Get condition type description
 */
public function description(): string
{
    return match($this) {
        self::TIME_RANGE => 'Allow only during specific hours (e.g., 9:00-18:00)',
        self::DAYS => 'Allow only on specific days of week',
        self::DATE_RANGE => 'Allow only between specific dates',
        
        self::IP_WHITELIST => 'Allow only from specific IP addresses',
        self::IP_BLACKLIST => 'Block specific IP addresses',
        
        self::REQUIRES_2FA => 'Require two-factor authentication',
        self::REQUIRES_EMAIL_VERIFIED => 'Require verified email address',
        
        self::MAX_AMOUNT => 'Limit maximum transaction amount',
        self::MIN_AMOUNT => 'Require minimum transaction amount',
        
        self::USER_ATTRIBUTES => 'Require specific user attributes (e.g., subscription: premium)',
        
        self::CUSTOM => 'Custom condition logic',
    };
}

/**
 * Get example value for condition type
 */
public function exampleValue(): mixed
{
    return match($this) {
        self::TIME_RANGE => ['start' => '09:00', 'end' => '18:00'],
        self::DAYS => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        self::DATE_RANGE => ['start' => '2025-01-01', 'end' => '2025-12-31'],
        
        self::IP_WHITELIST => ['192.168.1.0/24', '10.0.0.1'],
        self::IP_BLACKLIST => ['203.0.113.0/24'],
        
        self::REQUIRES_2FA => true,
        self::REQUIRES_EMAIL_VERIFIED => true,
        
        self::MAX_AMOUNT => 5000,
        self::MIN_AMOUNT => 100,
        
        self::USER_ATTRIBUTES => ['subscription' => 'premium', 'account_age_days' => 90],
        
        self::CUSTOM => ['custom_rule' => 'value'],
    };
}

/**
 * Get Heroicon for condition type
 */
public function icon(): string
{
    return match($this) {
        self::TIME_RANGE, self::DAYS, self::DATE_RANGE => 'heroicon-o-clock',
        self::IP_WHITELIST, self::IP_BLACKLIST => 'heroicon-o-globe-alt',
        self::REQUIRES_2FA => 'heroicon-o-shield-check',
        self::REQUIRES_EMAIL_VERIFIED => 'heroicon-o-envelope-open',
        self::MAX_AMOUNT, self::MIN_AMOUNT => 'heroicon-o-currency-dollar',
        self::USER_ATTRIBUTES => 'heroicon-o-user-circle',
        self::CUSTOM => 'heroicon-o-code-bracket',
    };
}

/**
 * Get color for Filament badge
 */
public function color(): string
{
    return match($this) {
        self::TIME_RANGE, self::DAYS, self::DATE_RANGE => 'info',
        self::IP_WHITELIST => 'success',
        self::IP_BLACKLIST => 'danger',
        self::REQUIRES_2FA, self::REQUIRES_EMAIL_VERIFIED => 'warning',
        self::MAX_AMOUNT, self::MIN_AMOUNT => 'primary',
        self::USER_ATTRIBUTES => 'gray',
        self::CUSTOM => 'purple',
    };
}

/**
 * Validate condition value structure
 */
public function validateValue(mixed $value): bool
{
    return match($this) {
        self::TIME_RANGE => is_array($value) && isset($value['start']) && isset($value['end']),
        self::DAYS => is_array($value) && !empty($value),
        self::DATE_RANGE => is_array($value) && isset($value['start']) && isset($value['end']),
        
        self::IP_WHITELIST, self::IP_BLACKLIST => is_array($value) && !empty($value),
        
        self::REQUIRES_2FA, self::REQUIRES_EMAIL_VERIFIED => is_bool($value),
        
        self::MAX_AMOUNT, self::MIN_AMOUNT => is_numeric($value) && $value > 0,
        
        self::USER_ATTRIBUTES => is_array($value) && !empty($value),
        
        self::CUSTOM => true, // Always valid for custom
    };
}
```

---

## ✅ CHECKLIST VALIDATION

Pour chaque enum :

- [ ] PHPDoc complet avec purpose
- [ ] Backed enum (: string)
- [ ] Cases avec commentaires
- [ ] Méthodes statiques (groups)
- [ ] Méthodes d'instance (metadata)
- [ ] description() avec match
- [ ] icon() avec Heroicons
- [ ] color() pour Filament
- [ ] Type hints partout
- [ ] < 200 lignes

---

## 🚀 COMMANDE

**Génère les 4 fichiers Enums dans :**
```
app/Enums/
```

**Nomenclature stricte :**
```
PermissionAction.php
WildcardPattern.php
AuditAction.php
ConditionType.php
```

**Chaque fichier doit :**
1. Utiliser PHP 8.1+ backed enums
2. Avoir PHPDoc exhaustif
3. Fournir méthodes helper
4. Fournir métadonnées riches (icon, color, description)
5. Être production-ready
6. < 200 lignes

---

**GO ! 🎯**
