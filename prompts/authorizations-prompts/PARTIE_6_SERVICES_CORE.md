# 🚀 PROMPT CLAUDE CODE - PARTIE 6 : SERVICES CORE

> **Contexte** : Créer services core pour gestion scopes, wildcards, conditions, et permissions

---

## 📋 OBJECTIF

Créer **4 fichiers de services** (3 nouveaux + 1 modification) pour implémenter la logique métier core du système de permissions.

**Principe** : Services avec injection de dépendances, méthodes type-safe, et gestion d'erreurs complète.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture**
- ✅ Injection de dépendances via constructeur
- ✅ Type hints partout (paramètres + return types)
- ✅ Single Responsibility Principle
- ✅ Exceptions custom pour erreurs métier

### **Performance**
- ✅ Cache Redis pour queries fréquentes
- ✅ Eager loading explicite
- ✅ Batch operations quand possible
- ✅ Query optimization (select, with)

### **Code Quality**
- ✅ PHPDoc exhaustif avec @throws
- ✅ Validation inputs
- ✅ Logs pour actions critiques
- ✅ < 300 lignes par fichier

---

## 📁 LISTE DES 4 SERVICES

### **Nouveaux Services (3)**
```
app/Services/Permissions/ScopeManager.php
app/Services/Permissions/WildcardExpander.php
app/Services/Permissions/ConditionEvaluator.php
```

### **Service à Modifier (1)**
```
app/Services/Permissions/PermissionChecker.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **SERVICE 1 : ScopeManager**

**Fichier** : `app/Services/Permissions/ScopeManager.php`

**Purpose** : Gestion centralisée des scopes (création, recherche, désactivation)

```php
<?php

namespace App\Services\Permissions;

use App\Models\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * ScopeManager Service
 *
 * Centralized scope management for the permission system
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class ScopeManager
{
    /**
     * Create or retrieve scope for a given entity
     *
     * @param Model $entity The entity to scope (Shop, Kitchen, etc.)
     * @param bool $activate Whether to activate if deactivated
     * @return Scope
     */
    public function createScopeForEntity(Model $entity, bool $activate = true): Scope
    {
        $scopeKey = $this->makeScopeKey($entity);
        
        // Try to find existing scope
        $scope = Scope::where('scopable_type', get_class($entity))
            ->where('scopable_id', $entity->id)
            ->first();
        
        if ($scope) {
            // Reactivate if needed
            if ($activate && !$scope->is_active) {
                $scope->activate();
            }
            
            return $scope;
        }
        
        // Create new scope
        return Scope::create([
            'scopable_type' => get_class($entity),
            'scopable_id' => $entity->id,
            'scope_key' => $scopeKey,
            'name' => $this->getScopeName($entity),
            'is_active' => $activate,
        ]);
    }

    /**
     * Find or create scope by key
     *
     * @param string $scopeKey Format: "type:id" (e.g., "shop:5")
     * @return Scope|null
     */
    public function findOrCreateScope(string $scopeKey): ?Scope
    {
        // Try cache first
        $cacheKey = "scope:{$scopeKey}";
        
        return Cache::remember($cacheKey, 3600, function () use ($scopeKey) {
            $scope = Scope::where('scope_key', $scopeKey)->first();
            
            if ($scope) {
                return $scope;
            }
            
            // Parse scope key
            [$type, $id] = $this->parseScopeKey($scopeKey);
            
            if (!$type || !$id) {
                return null;
            }
            
            // Find entity
            $modelClass = $this->getModelClass($type);
            
            if (!$modelClass || !class_exists($modelClass)) {
                return null;
            }
            
            $entity = $modelClass::find($id);
            
            if (!$entity) {
                return null;
            }
            
            return $this->createScopeForEntity($entity);
        });
    }

    /**
     * Get scope by ID with caching
     *
     * @param int $scopeId
     * @return Scope|null
     */
    public function getScopeById(int $scopeId): ?Scope
    {
        $cacheKey = "scope:id:{$scopeId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($scopeId) {
            return Scope::find($scopeId);
        });
    }

    /**
     * Deactivate a scope
     *
     * @param Scope $scope
     * @return bool
     */
    public function deactivateScope(Scope $scope): bool
    {
        $result = $scope->deactivate();
        
        if ($result) {
            // Invalidate cache
            Cache::forget("scope:{$scope->scope_key}");
            Cache::forget("scope:id:{$scope->id}");
        }
        
        return $result;
    }

    /**
     * Get all scopes by type
     *
     * @param string $type Entity type (shop, kitchen, etc.)
     * @param bool $activeOnly Only active scopes
     * @return Collection<Scope>
     */
    public function getScopesByType(string $type, bool $activeOnly = true): Collection
    {
        $modelClass = $this->getModelClass($type);
        
        $query = Scope::where('scopable_type', $modelClass);
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->get();
    }

    /**
     * Bulk create scopes for entities
     *
     * @param Collection $entities
     * @return Collection<Scope>
     */
    public function bulkCreateScopes(Collection $entities): Collection
    {
        $scopes = collect();
        
        foreach ($entities as $entity) {
            $scopes->push($this->createScopeForEntity($entity, false));
        }
        
        return $scopes;
    }

    /**
     * Make scope key from entity
     *
     * @param Model $entity
     * @return string
     */
    private function makeScopeKey(Model $entity): string
    {
        $type = strtolower(class_basename($entity));
        return "{$type}:{$entity->id}";
    }

    /**
     * Get scope name from entity
     *
     * @param Model $entity
     * @return string|null
     */
    private function getScopeName(Model $entity): ?string
    {
        return $entity->name ?? $entity->title ?? null;
    }

    /**
     * Parse scope key into type and id
     *
     * @param string $scopeKey
     * @return array [type, id]
     */
    private function parseScopeKey(string $scopeKey): array
    {
        $parts = explode(':', $scopeKey);
        
        if (count($parts) !== 2) {
            return [null, null];
        }
        
        return [$parts[0], (int) $parts[1]];
    }

    /**
     * Get model class from type
     *
     * @param string $type
     * @return string|null
     */
    private function getModelClass(string $type): ?string
    {
        return match($type) {
            'shop' => \App\Models\Shop::class,
            'kitchen' => \App\Models\Kitchen::class,
            'driver' => \App\Models\Driver::class,
            'supervisor' => \App\Models\Supervisor::class,
            'supplier' => \App\Models\Supplier::class,
            'user' => \App\Models\User::class,
            default => null,
        };
    }

    /**
     * Invalidate all scope caches
     *
     * @return void
     */
    public function invalidateAllCaches(): void
    {
        Cache::tags(['scopes'])->flush();
    }
}
```

---

### **SERVICE 2 : WildcardExpander**

**Fichier** : `app/Services/Permissions/WildcardExpander.php`

**Purpose** : Expansion automatique des wildcards en permissions concrètes

```php
<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use App\Models\PermissionWildcard;
use App\Models\PermissionTemplate;
use App\Enums\WildcardPattern;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WildcardExpander Service
 *
 * Expands wildcard patterns into concrete permissions
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class WildcardExpander
{
    /**
     * Expand a wildcard pattern into permissions
     *
     * @param string $pattern Wildcard pattern (e.g., "shops.*", "*.read")
     * @return Collection<Permission>
     */
    public function expand(string $pattern): Collection
    {
        // Full wildcard (*.*)
        if ($pattern === '*.*') {
            return Permission::all();
        }
        
        // Resource wildcard (shops.*)
        if (str_ends_with($pattern, '.*')) {
            $resource = str_replace('.*', '', $pattern);
            return Permission::where('slug', 'like', "{$resource}.%")->get();
        }
        
        // Action wildcard (*.read)
        if (str_starts_with($pattern, '*.')) {
            $action = str_replace('*.', '', $pattern);
            return Permission::where('slug', 'like', "%.{$action}")->get();
        }
        
        // Macro wildcard (specific pattern)
        return $this->expandMacro($pattern);
    }

    /**
     * Expand wildcard for a template
     *
     * @param PermissionTemplate $template
     * @return Collection<Permission>
     */
    public function expandForTemplate(PermissionTemplate $template): Collection
    {
        $permissions = collect();
        
        // Get all wildcards for this template
        $wildcards = $template->wildcards;
        
        foreach ($wildcards as $wildcard) {
            $expanded = $this->expand($wildcard->pattern);
            $permissions = $permissions->merge($expanded);
        }
        
        return $permissions->unique('id');
    }

    /**
     * Rebuild wildcard expansions (cache)
     *
     * @param PermissionWildcard $wildcard
     * @return int Number of permissions expanded
     */
    public function rebuildExpansions(PermissionWildcard $wildcard): int
    {
        DB::transaction(function () use ($wildcard) {
            // Clear existing expansions
            $wildcard->permissions()->detach();
            
            // Expand pattern
            $permissions = $this->expand($wildcard->pattern);
            
            // Attach with metadata
            $attachData = [];
            foreach ($permissions as $permission) {
                $attachData[$permission->id] = [
                    'is_auto_generated' => true,
                    'expanded_at' => now(),
                ];
            }
            
            $wildcard->permissions()->attach($attachData);
            
            // Update count
            $wildcard->markAsExpanded($permissions->count());
            
            Log::info("Wildcard expanded", [
                'wildcard_id' => $wildcard->id,
                'pattern' => $wildcard->pattern,
                'permissions_count' => $permissions->count(),
            ]);
        });
        
        return $wildcard->permissions_count;
    }

    /**
     * Check if permission matches pattern
     *
     * @param Permission $permission
     * @param string $pattern
     * @return bool
     */
    public function matchesPattern(Permission $permission, string $pattern): bool
    {
        // Full wildcard
        if ($pattern === '*.*') {
            return true;
        }
        
        // Resource wildcard (shops.*)
        if (str_ends_with($pattern, '.*')) {
            $resource = str_replace('.*', '', $pattern);
            return str_starts_with($permission->slug, "{$resource}.");
        }
        
        // Action wildcard (*.read)
        if (str_starts_with($pattern, '*.')) {
            $action = str_replace('*.', '', $pattern);
            return str_ends_with($permission->slug, ".{$action}");
        }
        
        // Macro pattern
        return $this->matchesMacro($permission, $pattern);
    }

    /**
     * Get all permissions matching multiple patterns
     *
     * @param array $patterns
     * @return Collection<Permission>
     */
    public function expandMultiple(array $patterns): Collection
    {
        $permissions = collect();
        
        foreach ($patterns as $pattern) {
            $expanded = $this->expand($pattern);
            $permissions = $permissions->merge($expanded);
        }
        
        return $permissions->unique('id');
    }

    /**
     * Auto-expand all active wildcards
     *
     * @return int Total permissions expanded
     */
    public function autoExpandAll(): int
    {
        $wildcards = PermissionWildcard::active()->autoExpand()->get();
        
        $totalCount = 0;
        
        foreach ($wildcards as $wildcard) {
            $count = $this->rebuildExpansions($wildcard);
            $totalCount += $count;
        }
        
        Log::info("Auto-expanded all wildcards", [
            'wildcards_count' => $wildcards->count(),
            'total_permissions' => $totalCount,
        ]);
        
        return $totalCount;
    }

    /**
     * Expand macro pattern (custom logic)
     *
     * @param string $pattern
     * @return Collection<Permission>
     */
    private function expandMacro(string $pattern): Collection
    {
        // Handle specific macro patterns
        return match($pattern) {
            'shops.read' => Permission::whereIn('slug', [
                'shops.list', 'shops.view', 'shops.read'
            ])->get(),
            
            'shops.write' => Permission::whereIn('slug', [
                'shops.create', 'shops.update', 'shops.delete'
            ])->get(),
            
            'shops.admin' => Permission::where('slug', 'like', 'shops.%')->get(),
            
            // Add more macros as needed
            default => collect(),
        };
    }

    /**
     * Check if permission matches macro pattern
     *
     * @param Permission $permission
     * @param string $pattern
     * @return bool
     */
    private function matchesMacro(Permission $permission, string $pattern): bool
    {
        $macroPermissions = $this->expandMacro($pattern);
        
        return $macroPermissions->contains('id', $permission->id);
    }
}
```

---

### **SERVICE 3 : ConditionEvaluator**

**Fichier** : `app/Services/Permissions/ConditionEvaluator.php`

**Purpose** : Évaluation des conditions contextuelles pour permissions

```php
<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Enums\ConditionType;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * ConditionEvaluator Service
 *
 * Evaluates contextual conditions for permission grants
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class ConditionEvaluator
{
    /**
     * Evaluate all conditions
     *
     * @param array $conditions Array of conditions to evaluate
     * @param User $user The user context
     * @param Request|null $request The request context
     * @return bool True if all conditions pass
     */
    public function evaluate(array $conditions, User $user, ?Request $request = null): bool
    {
        if (empty($conditions)) {
            return true;
        }
        
        foreach ($conditions as $type => $value) {
            try {
                $conditionType = ConditionType::from($type);
                
                if (!$this->evaluateCondition($conditionType, $value, $user, $request)) {
                    return false;
                }
            } catch (\ValueError $e) {
                // Unknown condition type, fail safe
                return false;
            }
        }
        
        return true;
    }

    /**
     * Evaluate single condition
     *
     * @param ConditionType $type
     * @param mixed $value
     * @param User $user
     * @param Request|null $request
     * @return bool
     */
    private function evaluateCondition(
        ConditionType $type,
        mixed $value,
        User $user,
        ?Request $request
    ): bool {
        return match($type) {
            ConditionType::TIME_RANGE => $this->evaluateTimeRange($value),
            ConditionType::DAYS => $this->evaluateDays($value),
            ConditionType::DATE_RANGE => $this->evaluateDateRange($value),
            ConditionType::IP_WHITELIST => $this->evaluateIpWhitelist($value, $request),
            ConditionType::IP_BLACKLIST => $this->evaluateIpBlacklist($value, $request),
            ConditionType::REQUIRES_2FA => $this->evaluateRequires2FA($value, $user),
            ConditionType::REQUIRES_EMAIL_VERIFIED => $this->evaluateEmailVerified($value, $user),
            ConditionType::MAX_AMOUNT => $this->evaluateMaxAmount($value, $request),
            ConditionType::MIN_AMOUNT => $this->evaluateMinAmount($value, $request),
            ConditionType::USER_ATTRIBUTES => $this->evaluateUserAttributes($value, $user),
            ConditionType::CUSTOM => $this->evaluateCustom($value, $user, $request),
        };
    }

    /**
     * Evaluate time range condition
     *
     * @param array $value ['start' => '09:00', 'end' => '18:00']
     * @return bool
     */
    private function evaluateTimeRange(array $value): bool
    {
        $now = now();
        $start = Carbon::createFromTimeString($value['start']);
        $end = Carbon::createFromTimeString($value['end']);
        
        return $now->between($start, $end);
    }

    /**
     * Evaluate days condition
     *
     * @param array $value ['monday', 'tuesday', ...]
     * @return bool
     */
    private function evaluateDays(array $value): bool
    {
        $today = strtolower(now()->englishDayOfWeek);
        
        return in_array($today, array_map('strtolower', $value));
    }

    /**
     * Evaluate date range condition
     *
     * @param array $value ['start' => '2025-01-01', 'end' => '2025-12-31']
     * @return bool
     */
    private function evaluateDateRange(array $value): bool
    {
        $now = now();
        $start = Carbon::parse($value['start']);
        $end = Carbon::parse($value['end']);
        
        return $now->between($start, $end);
    }

    /**
     * Evaluate IP whitelist condition
     *
     * @param array $value ['192.168.1.0/24', '10.0.0.1']
     * @param Request|null $request
     * @return bool
     */
    private function evaluateIpWhitelist(array $value, ?Request $request): bool
    {
        if (!$request) {
            return false;
        }
        
        $clientIp = $request->ip();
        
        foreach ($value as $allowedIp) {
            if ($this->ipMatches($clientIp, $allowedIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Evaluate IP blacklist condition
     *
     * @param array $value ['203.0.113.0/24']
     * @param Request|null $request
     * @return bool
     */
    private function evaluateIpBlacklist(array $value, ?Request $request): bool
    {
        if (!$request) {
            return true; // No request, can't be blacklisted
        }
        
        $clientIp = $request->ip();
        
        foreach ($value as $blockedIp) {
            if ($this->ipMatches($clientIp, $blockedIp)) {
                return false; // IP is blacklisted
            }
        }
        
        return true;
    }

    /**
     * Evaluate 2FA requirement
     *
     * @param bool $value
     * @param User $user
     * @return bool
     */
    private function evaluateRequires2FA(bool $value, User $user): bool
    {
        if (!$value) {
            return true;
        }
        
        return $user->two_factor_confirmed_at !== null;
    }

    /**
     * Evaluate email verification requirement
     *
     * @param bool $value
     * @param User $user
     * @return bool
     */
    private function evaluateEmailVerified(bool $value, User $user): bool
    {
        if (!$value) {
            return true;
        }
        
        return $user->hasVerifiedEmail();
    }

    /**
     * Evaluate max amount condition
     *
     * @param float $value
     * @param Request|null $request
     * @return bool
     */
    private function evaluateMaxAmount(float $value, ?Request $request): bool
    {
        if (!$request) {
            return true;
        }
        
        $amount = $request->input('amount', 0);
        
        return $amount <= $value;
    }

    /**
     * Evaluate min amount condition
     *
     * @param float $value
     * @param Request|null $request
     * @return bool
     */
    private function evaluateMinAmount(float $value, ?Request $request): bool
    {
        if (!$request) {
            return true;
        }
        
        $amount = $request->input('amount', 0);
        
        return $amount >= $value;
    }

    /**
     * Evaluate user attributes condition
     *
     * @param array $value ['subscription' => 'premium', 'account_age_days' => 90]
     * @param User $user
     * @return bool
     */
    private function evaluateUserAttributes(array $value, User $user): bool
    {
        foreach ($value as $attribute => $expectedValue) {
            $actualValue = data_get($user, $attribute);
            
            if ($actualValue !== $expectedValue) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Evaluate custom condition
     *
     * @param array $value
     * @param User $user
     * @param Request|null $request
     * @return bool
     */
    private function evaluateCustom(array $value, User $user, ?Request $request): bool
    {
        // Implement custom logic here
        // Could call external service, check database, etc.
        
        return true;
    }

    /**
     * Check if IP matches pattern (supports CIDR)
     *
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    private function ipMatches(string $ip, string $pattern): bool
    {
        // Simple IP match
        if ($ip === $pattern) {
            return true;
        }
        
        // CIDR notation
        if (str_contains($pattern, '/')) {
            [$subnet, $mask] = explode('/', $pattern);
            
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - (int) $mask);
            
            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }
        
        return false;
    }
}
```

---

### **SERVICE 4 : PermissionChecker (MODIFICATION)**

**Fichier** : `app/Services/Permissions/PermissionChecker.php`

**Modifications à apporter** :

```php
<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\Permission;
use App\Models\Scope;
use App\Models\PermissionDelegation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * PermissionChecker Service
 *
 * Check user permissions with scope and condition support
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionChecker
{
    public function __construct(
        private ConditionEvaluator $conditionEvaluator,
        private WildcardExpander $wildcardExpander,
        private ScopeManager $scopeManager
    ) {}

    /**
     * Check if user has permission with scope
     *
     * @param User $user
     * @param string $permissionSlug
     * @param Scope|int|null $scope
     * @param Request|null $request
     * @return bool
     */
    public function checkWithScope(
        User $user,
        string $permissionSlug,
        Scope|int|null $scope = null,
        ?Request $request = null
    ): bool {
        // Get scope instance
        $scopeInstance = $this->resolveScope($scope);
        
        // Check direct permissions
        if ($this->hasDirectPermission($user, $permissionSlug, $scopeInstance, $request)) {
            return true;
        }
        
        // Check template permissions
        if ($this->hasTemplatePermission($user, $permissionSlug, $scopeInstance, $request)) {
            return true;
        }
        
        // Check delegated permissions
        if ($this->hasDelegatedPermission($user, $permissionSlug, $scopeInstance, $request)) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user has permission with conditions
     *
     * @param User $user
     * @param string $permissionSlug
     * @param array $conditions
     * @param Request|null $request
     * @return bool
     */
    public function checkWithConditions(
        User $user,
        string $permissionSlug,
        array $conditions,
        ?Request $request = null
    ): bool {
        // First check if user has permission at all
        if (!$this->checkWithScope($user, $permissionSlug, null, $request)) {
            return false;
        }
        
        // Then evaluate conditions
        return $this->conditionEvaluator->evaluate($conditions, $user, $request);
    }

    /**
     * Check if user has delegated permission
     *
     * @param User $user
     * @param string $permissionSlug
     * @param Scope|null $scope
     * @param Request|null $request
     * @return bool
     */
    public function hasDelegatedPermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope = null,
        ?Request $request = null
    ): bool {
        $delegation = PermissionDelegation::active()
            ->where('delegatee_id', $user->id)
            ->where('permission_slug', $permissionSlug)
            ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
            ->first();
        
        return $delegation !== null;
    }

    /**
     * Get all user permissions with scope
     *
     * @param User $user
     * @param Scope|null $scope
     * @return Collection<Permission>
     */
    public function getAllUserPermissions(User $user, ?Scope $scope = null): Collection
    {
        $cacheKey = "user:{$user->id}:permissions:" . ($scope?->id ?? 'global');
        
        return Cache::remember($cacheKey, 600, function () use ($user, $scope) {
            $permissions = collect();
            
            // Direct permissions
            $directPerms = $user->permissions()
                ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
                ->get();
            
            $permissions = $permissions->merge($directPerms);
            
            // Template permissions
            $templates = $user->templates()
                ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
                ->with(['permissions', 'wildcards'])
                ->get();
            
            foreach ($templates as $template) {
                // Direct template permissions
                $permissions = $permissions->merge($template->getAllPermissions());
                
                // Wildcard expanded permissions
                $wildcardPerms = $this->wildcardExpander->expandForTemplate($template);
                $permissions = $permissions->merge($wildcardPerms);
            }
            
            // Delegated permissions
            $delegations = PermissionDelegation::active()
                ->where('delegatee_id', $user->id)
                ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
                ->with('permission')
                ->get();
            
            foreach ($delegations as $delegation) {
                $permissions->push($delegation->permission);
            }
            
            return $permissions->unique('id');
        });
    }

    /**
     * Check direct permission
     */
    private function hasDirectPermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope,
        ?Request $request
    ): bool {
        $userPerm = $user->permissions()
            ->where('slug', $permissionSlug)
            ->when($scope, fn($q) => $q->where('user_permissions.scope_id', $scope->id))
            ->first();
        
        if (!$userPerm) {
            return false;
        }
        
        // Evaluate conditions if present
        $conditions = $userPerm->pivot->conditions ?? [];
        
        if (!empty($conditions)) {
            return $this->conditionEvaluator->evaluate($conditions, $user, $request);
        }
        
        return true;
    }

    /**
     * Check template permission
     */
    private function hasTemplatePermission(
        User $user,
        string $permissionSlug,
        ?Scope $scope,
        ?Request $request
    ): bool {
        $templates = $user->templates()
            ->when($scope, fn($q) => $q->where('user_templates.scope_id', $scope->id))
            ->with(['permissions', 'wildcards'])
            ->get();
        
        foreach ($templates as $template) {
            // Check direct permissions
            if ($template->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
            
            // Check wildcard patterns
            foreach ($template->wildcards as $wildcard) {
                $permission = Permission::where('slug', $permissionSlug)->first();
                
                if ($permission && $this->wildcardExpander->matchesPattern($permission, $wildcard->pattern)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Resolve scope from various inputs
     */
    private function resolveScope(Scope|int|null $scope): ?Scope
    {
        if ($scope instanceof Scope) {
            return $scope;
        }
        
        if (is_int($scope)) {
            return $this->scopeManager->getScopeById($scope);
        }
        
        return null;
    }

    /**
     * Invalidate user permission cache
     */
    public function invalidateUserCache(User $user): void
    {
        Cache::forget("user:{$user->id}:permissions:global");
        
        // Also invalidate all scope-specific caches
        $scopes = Scope::all();
        foreach ($scopes as $scope) {
            Cache::forget("user:{$user->id}:permissions:{$scope->id}");
        }
    }
}
```

---

## ✅ CHECKLIST VALIDATION

Pour chaque service :

- [ ] PHPDoc complet avec @param, @return, @throws
- [ ] Type hints partout (paramètres + return)
- [ ] Injection de dépendances via constructeur
- [ ] Validation des inputs
- [ ] Cache pour queries fréquentes
- [ ] Logs pour actions critiques
- [ ] Gestion d'erreurs (try-catch, null checks)
- [ ] < 300 lignes

---

## 🚀 COMMANDE

**Génère les 4 fichiers dans :**
```
app/Services/Permissions/
```

**3 Nouveaux Services :**
```
ScopeManager.php
WildcardExpander.php
ConditionEvaluator.php
```

**1 Service à Modifier :**
```
PermissionChecker.php
```

**Chaque fichier doit :**
1. Avoir PHPDoc exhaustif
2. Type hints complets
3. Cache Redis quand approprié
4. Logs pour actions critiques
5. Gestion d'erreurs robuste
6. Être production-ready

---

**GO ! 🎯**
