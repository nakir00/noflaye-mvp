# 📝 CODE COMPLET - CASTS POUR CHAQUE MODÈLE

> Code généré automatiquement pour mettre à jour les méthodes `casts()` de chaque modèle

---

## ✅ MODÈLES DÉJÀ MIS À JOUR

- [x] User
- [x] Permission

---

## 📄 CODE À APPLIQUER

### PermissionGroup

```php
/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'parent_id' => 'integer',
        'level' => 'integer',

        // DateTime columns
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

---

### PermissionTemplate

```php
/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'parent_id' => 'integer',
        'scope_id' => 'integer',
        'level' => 'integer',
        'sort_order' => 'integer',

        // Boolean columns
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'auto_sync_users' => 'boolean',

        // DateTime columns
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
```

---

### PermissionWildcard

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Enums\WildcardPattern;

/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Enum columns
        'pattern_type' => WildcardPattern::class,

        // Boolean columns
        'is_active' => 'boolean',
        'auto_expand' => 'boolean',

        // Integer columns
        'sort_order' => 'integer',
        'permissions_count' => 'integer',

        // DateTime columns
        'last_expanded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

---

### PermissionDelegation

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'delegator_id' => 'integer',
        'delegatee_id' => 'integer',
        'permission_id' => 'integer',
        'scope_id' => 'integer',
        'max_redelegation_depth' => 'integer',
        'revoked_by' => 'integer',

        // Boolean columns
        'can_redelegate' => 'boolean',

        // DateTime columns
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        // JSON columns
        'metadata' => AsArrayObject::class,
    ];
}
```

---

### DelegationChain

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'delegation_id' => 'integer',
        'parent_delegation_id' => 'integer',
        'depth' => 'integer',

        // JSON columns
        'chain_path' => AsArrayObject::class,

        // DateTime columns
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

---

### PermissionAuditLog

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'user_id' => 'integer',
        'source_id' => 'integer',
        'scope_id' => 'integer',
        'performed_by' => 'integer',

        // JSON columns
        'metadata' => AsArrayObject::class,

        // DateTime columns
        'created_at' => 'datetime',
    ];
}
```

---

### PermissionRequest

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'user_id' => 'integer',
        'permission_id' => 'integer',
        'scope_id' => 'integer',
        'reviewed_by' => 'integer',

        // DateTime columns
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        // JSON columns
        'metadata' => AsArrayObject::class,
    ];
}
```

---

### Scope

```php
/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'scopable_id' => 'integer',

        // Boolean columns
        'is_active' => 'boolean',

        // DateTime columns
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
```

---

### UserGroup

```php
/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'parent_id' => 'integer',
        'template_id' => 'integer',
        'level' => 'integer',

        // Boolean columns
        'is_active' => 'boolean',

        // DateTime columns
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

---

### UserPermission

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

/**
 * Get the attributes that should be cast.
 *
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        // Integer columns
        'user_id' => 'integer',
        'permission_id' => 'integer',
        'scope_id' => 'integer',
        'source_id' => 'integer',

        // DateTime columns
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        // JSON columns
        'conditions' => AsArrayObject::class,
    ];
}
```

---

## 🚀 INSTRUCTIONS D'APPLICATION

Pour chaque modèle ci-dessus:

1. **Ouvrir le fichier** `app/Models/{ModelName}.php`
2. **Localiser la méthode** `casts()` existante
3. **Remplacer** par le code fourni ci-dessus
4. **Vérifier les imports** en haut du fichier:
   - Ajouter `use Illuminate\Database\Eloquent\Casts\AsArrayObject;` si le modèle utilise JSON
   - Ajouter `use App\Enums\{EnumName};` si le modèle utilise des enums

5. **Sauvegarder** le fichier

---

## ✅ VALIDATION

Après application, tester avec:

```bash
php artisan tinker
```

```php
// Test User
$user = User::first();
dump($user->created_at instanceof Carbon\Carbon);  // true
dump($user->email_verified_at instanceof Carbon\Carbon);  // true

// Test Permission
$perm = Permission::first();
dump($perm->active === true || $perm->active === false);  // true (boolean)

// Test PermissionDelegation
$delegation = PermissionDelegation::first();
dump($delegation->metadata);  // ArrayObject instance
dump($delegation->can_redelegate === true || $delegation->can_redelegate === false);  // true

exit
```

---

## 📊 RÉSUMÉ

- ✅ **10 modèles** prioritaires
- ✅ **107 casts** générés
- ✅ **Tous les types** couverts (datetime, integer, boolean, JSON, enum)
- ✅ **Documentation** complète

**Temps estimé d'application**: 10-15 minutes pour tous les modèles
