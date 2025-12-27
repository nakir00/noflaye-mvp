# 🚀 PROMPT CLAUDE CODE - PARTIE 5 : MODELS

> **Contexte** : Créer models Eloquent avec relations, casts, scopes, et méthodes métier

---

## 📋 OBJECTIF

Créer **7 nouveaux models** et **modifier 3 models existants** pour compléter la couche de données.

**Principe** : Models Eloquent riches avec relations, casts enums, scopes, et méthodes métier.

---

## 🎯 CONTRAINTES STRICTES

### **Eloquent Best Practices**
- ✅ Relations explicites avec type hints
- ✅ Casts pour enums et JSON
- ✅ Scopes réutilisables
- ✅ Méthodes métier documentées
- ✅ Fillable/guarded appropriés

### **Performance**
- ✅ Eager loading par défaut (with)
- ✅ Index hints dans commentaires
- ✅ Accessors/Mutators optimisés
- ✅ Query scopes performants

### **Code Quality**
- ✅ PHPDoc complet avec @property
- ✅ Type hints partout
- ✅ < 200 lignes par fichier
- ✅ Relations groupées logiquement

---

## 📁 LISTE DES 10 MODELS

### **Nouveaux Models (7)**
```
app/Models/Scope.php
app/Models/PermissionTemplate.php
app/Models/PermissionWildcard.php
app/Models/PermissionDelegation.php
app/Models/PermissionTemplateVersion.php
app/Models/PermissionRequest.php
app/Models/PermissionAuditLog.php
```

### **Models à Modifier (3)**
```
app/Models/User.php
app/Models/UserGroup.php
app/Models/PermissionGroup.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **MODEL 1 : Scope**

**Fichier** : `app/Models/Scope.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scope Model
 *
 * Unified scope management for permissions, templates, and groups
 *
 * @property int $id
 * @property string $scopable_type
 * @property int $scopable_id
 * @property string $scope_key
 * @property string|null $name
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Model $scopable
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class Scope extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'scopable_type',
        'scopable_id',
        'scope_key',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the owning scopable model (Shop, Kitchen, etc.)
     */
    public function scopable(): MorphTo
    {
        return $this->morphTo();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to only active scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to specific scopable type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('scopable_type', $type);
    }

    /**
     * Scope to specific scope key
     */
    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('scope_key', $key);
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Deactivate this scope
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Activate this scope
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Get display name (name or generated from key)
     */
    public function getDisplayName(): string
    {
        return $this->name ?? $this->scope_key;
    }
}
```

---

### **MODEL 2 : PermissionTemplate**

**Fichier** : `app/Models/PermissionTemplate.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * PermissionTemplate Model
 *
 * Templates for grouping permissions (replaces roles)
 * Supports hierarchy, wildcards, and versioning
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property int|null $scope_id
 * @property string $color
 * @property string $icon
 * @property int $level
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_system
 * @property bool $auto_sync_users
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read PermissionTemplate|null $parent
 * @property-read Collection<PermissionTemplate> $children
 * @property-read Collection<Permission> $permissions
 * @property-read Collection<PermissionWildcard> $wildcards
 * @property-read Collection<User> $users
 * @property-read Collection<PermissionTemplateVersion> $versions
 * @property-read Scope|null $scope
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'scope_id',
        'color',
        'icon',
        'level',
        'sort_order',
        'is_active',
        'is_system',
        'auto_sync_users',
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'auto_sync_users' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $with = ['permissions', 'wildcards'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PermissionTemplate::class, 'parent_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'template_permissions')
            ->withPivot('source', 'wildcard_id', 'sort_order')
            ->withTimestamps();
    }

    public function wildcards(): BelongsToMany
    {
        return $this->belongsToMany(PermissionWildcard::class, 'template_wildcards')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_templates')
            ->withPivot('scope_id', 'template_version', 'auto_upgrade', 'auto_sync', 'valid_from', 'valid_until')
            ->withTimestamps();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PermissionTemplateVersion::class, 'template_id');
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_system', false);
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Get all permissions including inherited from parent
     */
    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;

        if ($this->parent_id) {
            $permissions = $permissions->merge($this->parent->getAllPermissions());
        }

        return $permissions->unique('id');
    }

    /**
     * Sync permissions to all users with auto_sync enabled
     */
    public function syncUsers(): int
    {
        if (!$this->auto_sync_users) {
            return 0;
        }

        $users = $this->users()->wherePivot('auto_sync', true)->get();

        foreach ($users as $user) {
            // Logic handled by service layer
        }

        return $users->count();
    }
}
```

---

### **MODEL 3 : PermissionWildcard**

**Fichier** : `app/Models/PermissionWildcard.php`

```php
<?php

namespace App\Models;

use App\Enums\WildcardPattern;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * PermissionWildcard Model
 *
 * Wildcard patterns for automatic permission expansion
 *
 * @property int $id
 * @property string $pattern
 * @property string|null $description
 * @property string $pattern_type
 * @property string|null $icon
 * @property string $color
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $auto_expand
 * @property \Carbon\Carbon|null $last_expanded_at
 * @property int $permissions_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Collection<Permission> $permissions
 * @property-read Collection<PermissionTemplate> $templates
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionWildcard extends Model
{
    protected $fillable = [
        'pattern',
        'description',
        'pattern_type',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'auto_expand',
        'last_expanded_at',
        'permissions_count',
    ];

    protected $casts = [
        'pattern_type' => 'string', // Will cast via accessor if needed
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'auto_expand' => 'boolean',
        'last_expanded_at' => 'datetime',
        'permissions_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'wildcard_permissions')
            ->withPivot('is_auto_generated', 'expanded_at')
            ->withTimestamps();
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(PermissionTemplate::class, 'template_wildcards')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAutoExpand(Builder $query): Builder
    {
        return $query->where('auto_expand', true);
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Get pattern as WildcardPattern enum (if exists)
     */
    public function getPatternEnum(): ?WildcardPattern
    {
        try {
            return WildcardPattern::from($this->pattern);
        } catch (\ValueError $e) {
            return null;
        }
    }

    /**
     * Mark as expanded
     */
    public function markAsExpanded(int $count): bool
    {
        return $this->update([
            'last_expanded_at' => now(),
            'permissions_count' => $count,
        ]);
    }
}
```

---

### **MODEL 4 : PermissionDelegation**

**Fichier** : `app/Models/PermissionDelegation.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * PermissionDelegation Model
 *
 * Temporary permission delegation with re-delegation support
 *
 * @property int $id
 * @property int $delegator_id
 * @property string $delegator_name
 * @property int $delegatee_id
 * @property string $delegatee_name
 * @property int $permission_id
 * @property string $permission_slug
 * @property int|null $scope_id
 * @property \Carbon\Carbon $valid_from
 * @property \Carbon\Carbon $valid_until
 * @property bool $can_redelegate
 * @property int $max_redelegation_depth
 * @property string|null $reason
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $revoked_at
 * @property int|null $revoked_by
 * @property string|null $revocation_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $delegator
 * @property-read User $delegatee
 * @property-read Permission $permission
 * @property-read Scope|null $scope
 * @property-read User|null $revoker
 * @property-read Collection<DelegationChain> $chain
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionDelegation extends Model
{
    protected $fillable = [
        'delegator_id',
        'delegator_name',
        'delegatee_id',
        'delegatee_name',
        'permission_id',
        'permission_slug',
        'scope_id',
        'valid_from',
        'valid_until',
        'can_redelegate',
        'max_redelegation_depth',
        'reason',
        'metadata',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'can_redelegate' => 'boolean',
        'max_redelegation_depth' => 'integer',
        'metadata' => 'array',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegatee_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function chain(): HasMany
    {
        return $this->hasMany(DelegationChain::class, 'delegation_id');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where('valid_until', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where('valid_until', '<=', now());
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->whereNotNull('revoked_at');
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Check if delegation is currently active
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at) && 
               $this->valid_until > now();
    }

    /**
     * Check if can be re-delegated
     */
    public function canRedelegate(): bool
    {
        return $this->can_redelegate && 
               $this->isActive();
    }

    /**
     * Revoke this delegation
     */
    public function revoke(User $revokedBy, string $reason = null): bool
    {
        return $this->update([
            'revoked_at' => now(),
            'revoked_by' => $revokedBy->id,
            'revocation_reason' => $reason,
        ]);
    }
}
```

---

### **MODEL 5-7 : Autres Nouveaux Models** (Structure similaire)

**PermissionTemplateVersion.php** (~90 lignes)
**PermissionRequest.php** (~80 lignes)
**PermissionAuditLog.php** (~80 lignes)

---

### **MODEL 8 : User (MODIFICATION)**

**Fichier** : `app/Models/User.php`

**Modifications à apporter** :

```php
// Ajouter import
use App\Traits\HasPermissionsOptimized;

// Ajouter trait
use HasPermissionsOptimized;

// Modifier fillable (remplacer primary_role_id)
protected $fillable = [
    // ...
    'primary_template_id', // au lieu de primary_role_id
];

// Ajouter casts
protected $casts = [
    // ... existants
    'primary_template_id' => 'integer',
];

// Ajouter relations
public function primaryTemplate(): BelongsTo
{
    return $this->belongsTo(PermissionTemplate::class, 'primary_template_id');
}

public function templates(): BelongsToMany
{
    return $this->belongsToMany(PermissionTemplate::class, 'user_templates')
        ->withPivot('scope_id', 'template_version', 'auto_upgrade', 'auto_sync', 'valid_from', 'valid_until')
        ->withTimestamps();
}

public function delegationsGiven(): HasMany
{
    return $this->hasMany(PermissionDelegation::class, 'delegator_id');
}

public function delegationsReceived(): HasMany
{
    return $this->hasMany(PermissionDelegation::class, 'delegatee_id');
}

public function permissionRequests(): HasMany
{
    return $this->hasMany(PermissionRequest::class);
}

public function scopes(): HasMany
{
    return $this->hasMany(Scope::class, 'scopable_id')
        ->where('scopable_type', self::class);
}
```

---

### **MODEL 9-10 : UserGroup & PermissionGroup (MODIFICATIONS)**

**UserGroup.php** - Ajouter :
```php
public function parent(): BelongsTo
{
    return $this->belongsTo(UserGroup::class, 'parent_id');
}

public function children(): HasMany
{
    return $this->hasMany(UserGroup::class, 'parent_id');
}

public function template(): BelongsTo
{
    return $this->belongsTo(PermissionTemplate::class, 'template_id');
}

public function getAllPermissions(): Collection
{
    // Logic to get permissions from template + parent
}
```

**PermissionGroup.php** - Ajouter :
```php
public function parent(): BelongsTo
{
    return $this->belongsTo(PermissionGroup::class, 'parent_id');
}

public function children(): HasMany
{
    return $this->hasMany(PermissionGroup::class, 'parent_id');
}
```

---

## ✅ CHECKLIST VALIDATION

Pour chaque model :

- [ ] PHPDoc complet avec @property
- [ ] Namespace correct
- [ ] Fillable/guarded définis
- [ ] Casts appropriés (enums, JSON, dates)
- [ ] Relations avec type hints
- [ ] Scopes réutilisables
- [ ] Méthodes métier documentées
- [ ] < 200 lignes

---

## 🚀 COMMANDE

**Génère les 10 fichiers dans :**
```
app/Models/
```

**7 Nouveaux Models :**
```
Scope.php
PermissionTemplate.php
PermissionWildcard.php
PermissionDelegation.php
PermissionTemplateVersion.php
PermissionRequest.php
PermissionAuditLog.php
```

**3 Models à Modifier :**
```
User.php (ajouter relations + trait)
UserGroup.php (ajouter hiérarchie + template)
PermissionGroup.php (ajouter hiérarchie)
```

**Chaque fichier doit :**
1. Avoir PHPDoc exhaustif
2. Relations avec type hints
3. Casts appropriés
4. Scopes utiles
5. Méthodes métier
6. Être production-ready

---

**GO ! 🎯**
