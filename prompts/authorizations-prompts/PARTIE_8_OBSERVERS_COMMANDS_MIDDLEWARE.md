# 🚀 PROMPT CLAUDE CODE - PARTIE 8 : OBSERVERS, COMMANDS, MIDDLEWARE

> **Contexte** : Créer observers pour auto-sync, commands pour maintenance, middleware pour rate limiting, et trait pour API user-friendly

---

## 📋 OBJECTIF

Créer **12 fichiers** (5 observers + 1 trait + 1 middleware + 5 commands) pour automatiser la gestion des permissions.

**Principe** : Observers pour événements, Commands pour tâches planifiées, Middleware pour sécurité, Trait pour simplification API.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture**
- ✅ Observers avec injection de services
- ✅ Commands avec progress bars
- ✅ Middleware avec rate limiting
- ✅ Trait utilisant PermissionChecker service

### **Performance**
- ✅ Observers optimisés (éviter N+1)
- ✅ Commands avec chunks
- ✅ Cache invalidation appropriée
- ✅ Bulk operations

### **Code Quality**
- ✅ PHPDoc exhaustif
- ✅ Type hints partout
- ✅ Logs appropriés
- ✅ < 150 lignes par fichier

---

## 📁 LISTE DES 12 FICHIERS

### **Observers (5)**
```
app/Observers/UserGroupObserver.php
app/Observers/PermissionTemplateObserver.php
app/Observers/PermissionGroupObserver.php
app/Observers/PermissionObserver.php
app/Observers/UserPermissionObserver.php
```

### **Trait (1)**
```
app/Traits/HasPermissionsOptimized.php
```

### **Middleware (1)**
```
app/Http/Middleware/PermissionRateLimiter.php
```

### **Commands (5)**
```
app/Console/Commands/ExpirePermissionsCommand.php
app/Console/Commands/ExpireDelegationsCommand.php
app/Console/Commands/RebuildHierarchiesCommand.php
app/Console/Commands/WarmPermissionCacheCommand.php
app/Console/Commands/CleanupAuditLogCommand.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **OBSERVER 1 : UserGroupObserver**

**Fichier** : `app/Observers/UserGroupObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\UserGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UserGroupObserver
 *
 * Handle UserGroup lifecycle events
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class UserGroupObserver
{
    /**
     * Handle the UserGroup "created" event
     */
    public function created(UserGroup $userGroup): void
    {
        // Rebuild hierarchy if has parent
        if ($userGroup->parent_id) {
            $this->rebuildHierarchyForGroup($userGroup);
        }

        // Auto-assign template if configured
        if ($userGroup->template_id && $userGroup->auto_sync_template) {
            $this->syncTemplateToUsers($userGroup);
        }

        Log::info('UserGroup created', [
            'group_id' => $userGroup->id,
            'name' => $userGroup->name,
            'parent_id' => $userGroup->parent_id,
        ]);
    }

    /**
     * Handle the UserGroup "updated" event
     */
    public function updated(UserGroup $userGroup): void
    {
        // Rebuild hierarchy if parent changed
        if ($userGroup->isDirty('parent_id')) {
            $this->rebuildHierarchyForGroup($userGroup);
            
            Log::info('UserGroup hierarchy changed', [
                'group_id' => $userGroup->id,
                'old_parent_id' => $userGroup->getOriginal('parent_id'),
                'new_parent_id' => $userGroup->parent_id,
            ]);
        }

        // Re-sync template if changed or auto_sync enabled
        if ($userGroup->isDirty('template_id') && $userGroup->auto_sync_template) {
            $this->syncTemplateToUsers($userGroup);
        }
    }

    /**
     * Handle the UserGroup "deleting" event
     */
    public function deleting(UserGroup $userGroup): void
    {
        // Soft delete children groups
        $userGroup->children()->delete();

        Log::info('UserGroup deleting with children', [
            'group_id' => $userGroup->id,
            'children_count' => $userGroup->children()->count(),
        ]);
    }

    /**
     * Rebuild hierarchy for group
     */
    private function rebuildHierarchyForGroup(UserGroup $userGroup): void
    {
        // Clear existing hierarchy
        DB::table('user_group_hierarchy')
            ->where('descendant_id', $userGroup->id)
            ->delete();

        // Rebuild ancestors
        $ancestors = $this->findAncestors($userGroup->id);

        foreach ($ancestors as $depth => $ancestorId) {
            DB::table('user_group_hierarchy')->insert([
                'ancestor_id' => $ancestorId,
                'descendant_id' => $userGroup->id,
                'depth' => $depth,
            ]);
        }

        // Recalculate level
        $level = count($ancestors);
        $userGroup->update(['level' => $level]);
    }

    /**
     * Find all ancestors recursively
     */
    private function findAncestors(int $groupId, int $depth = 0): array
    {
        $ancestors = [];

        $parent = DB::table('user_groups')
            ->where('id', $groupId)
            ->value('parent_id');

        if ($parent) {
            $ancestors[$depth] = $parent;
            $ancestors = array_merge($ancestors, $this->findAncestors($parent, $depth + 1));
        }

        return $ancestors;
    }

    /**
     * Sync template permissions to all group users
     */
    private function syncTemplateToUsers(UserGroup $userGroup): void
    {
        if (!$userGroup->template_id) {
            return;
        }

        // This will be handled by a job in production
        // For now, just log
        Log::info('Template sync needed', [
            'group_id' => $userGroup->id,
            'template_id' => $userGroup->template_id,
            'users_count' => $userGroup->users()->count(),
        ]);
    }
}
```

---

### **OBSERVER 2 : PermissionTemplateObserver**

**Fichier** : `app/Observers/PermissionTemplateObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\PermissionTemplate;
use App\Services\Permissions\WildcardExpander;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PermissionTemplateObserver
 *
 * Handle PermissionTemplate lifecycle events
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionTemplateObserver
{
    public function __construct(
        private WildcardExpander $wildcardExpander,
        private PermissionChecker $permissionChecker
    ) {}

    /**
     * Handle the PermissionTemplate "saved" event
     */
    public function saved(PermissionTemplate $template): void
    {
        // Expand wildcards if changed
        if ($template->wasRecentlyCreated || $template->wasChanged()) {
            foreach ($template->wildcards as $wildcard) {
                $this->wildcardExpander->rebuildExpansions($wildcard);
            }
        }

        // Sync users if auto_sync enabled and permissions changed
        if ($template->auto_sync_users && $template->wasChanged()) {
            $this->syncUsersWithTemplate($template);
        }

        // Rebuild hierarchy if parent changed
        if ($template->isDirty('parent_id')) {
            $this->rebuildHierarchyForTemplate($template);
        }
    }

    /**
     * Handle the PermissionTemplate "deleting" event
     */
    public function deleting(PermissionTemplate $template): bool
    {
        // Prevent deletion if users assigned
        $usersCount = $template->users()->count();

        if ($usersCount > 0) {
            Log::warning('Attempted to delete template with users', [
                'template_id' => $template->id,
                'users_count' => $usersCount,
            ]);

            throw new \Exception("Cannot delete template with {$usersCount} users assigned");
        }

        // Soft delete children templates
        $template->children()->delete();

        return true;
    }

    /**
     * Sync template to all assigned users
     */
    private function syncUsersWithTemplate(PermissionTemplate $template): void
    {
        $users = $template->users()
            ->wherePivot('auto_sync', true)
            ->get();

        foreach ($users as $user) {
            // Invalidate user permission cache
            $this->permissionChecker->invalidateUserCache($user);
        }

        Log::info('Template synced to users', [
            'template_id' => $template->id,
            'users_count' => $users->count(),
        ]);
    }

    /**
     * Rebuild hierarchy for template
     */
    private function rebuildHierarchyForTemplate(PermissionTemplate $template): void
    {
        DB::table('permission_template_hierarchy')
            ->where('descendant_id', $template->id)
            ->delete();

        $ancestors = $this->findAncestors($template->id);

        foreach ($ancestors as $depth => $ancestorId) {
            DB::table('permission_template_hierarchy')->insert([
                'ancestor_id' => $ancestorId,
                'descendant_id' => $template->id,
                'depth' => $depth,
            ]);
        }

        $level = count($ancestors);
        $template->update(['level' => $level]);
    }

    /**
     * Find all ancestors recursively
     */
    private function findAncestors(int $templateId, int $depth = 0): array
    {
        $ancestors = [];

        $parent = DB::table('permission_templates')
            ->where('id', $templateId)
            ->value('parent_id');

        if ($parent) {
            $ancestors[$depth] = $parent;
            $ancestors = array_merge($ancestors, $this->findAncestors($parent, $depth + 1));
        }

        return $ancestors;
    }
}
```

---

### **OBSERVERS 3-5** : Structure similaire

**PermissionGroupObserver.php** (~80 lignes) - Hiérarchie
**PermissionObserver.php** (~80 lignes) - Wildcard rebuild
**UserPermissionObserver.php** (~100 lignes) - Audit logging

---

### **TRAIT : HasPermissionsOptimized**

**Fichier** : `app/Traits/HasPermissionsOptimized.php`

```php
<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Scope;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Collection;

/**
 * HasPermissionsOptimized Trait
 *
 * Provides optimized permission checking methods for User model
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
trait HasPermissionsOptimized
{
    /**
     * Check if user can perform action
     *
     * @param string $permission Permission slug
     * @param Scope|int|string|null $scope Scope (instance, id, or key)
     * @return bool
     */
    public function can($permission, $scope = null): bool
    {
        $checker = app(PermissionChecker::class);

        // Resolve scope
        $scopeInstance = $this->resolveScope($scope);

        return $checker->checkWithScope($this, $permission, $scopeInstance);
    }

    /**
     * Check if user has template assigned
     *
     * @param string $templateSlug Template slug
     * @return bool
     */
    public function hasTemplate(string $templateSlug): bool
    {
        return $this->templates()
            ->where('slug', $templateSlug)
            ->exists();
    }

    /**
     * Check if user has active delegation for permission
     *
     * @param string $permission Permission slug
     * @param Scope|null $scope Scope context
     * @return bool
     */
    public function hasDelegation(string $permission, ?Scope $scope = null): bool
    {
        $checker = app(PermissionChecker::class);

        return $checker->hasDelegatedPermission($this, $permission, $scope);
    }

    /**
     * Get all user permissions with scope
     *
     * @param Scope|int|string|null $scope Scope filter
     * @return Collection<Permission>
     */
    public function getAllPermissions($scope = null): Collection
    {
        $checker = app(PermissionChecker::class);

        $scopeInstance = $this->resolveScope($scope);

        return $checker->getAllUserPermissions($this, $scopeInstance);
    }

    /**
     * Check if user can perform any of the given permissions
     *
     * @param array $permissions Array of permission slugs
     * @param Scope|null $scope Scope context
     * @return bool
     */
    public function canAny(array $permissions, $scope = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission, $scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can perform all of the given permissions
     *
     * @param array $permissions Array of permission slugs
     * @param Scope|null $scope Scope context
     * @return bool
     */
    public function canAll(array $permissions, $scope = null): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission, $scope)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve scope from various inputs
     *
     * @param Scope|int|string|null $scope
     * @return Scope|null
     */
    private function resolveScope($scope): ?Scope
    {
        if ($scope instanceof Scope) {
            return $scope;
        }

        if (is_int($scope)) {
            return Scope::find($scope);
        }

        if (is_string($scope)) {
            return Scope::where('scope_key', $scope)->first();
        }

        return null;
    }
}
```

---

### **MIDDLEWARE : PermissionRateLimiter**

**Fichier** : `app/Http/Middleware/PermissionRateLimiter.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionRateLimiter Middleware
 *
 * Rate limit permission checks per user
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionRateLimiter
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission Permission being checked
     * @param int $maxAttempts Maximum attempts (default: 60)
     * @param int $decayMinutes Decay time in minutes (default: 1)
     * @return Response
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission,
        int $maxAttempts = 60,
        int $decayMinutes = 1
    ): Response {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($user->id, $permission, $request->ip());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->logRateLimitExceeded($user, $permission, $request);

            return response()->json([
                'message' => 'Too many permission checks. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }

    /**
     * Resolve request signature
     */
    private function resolveRequestSignature(int $userId, string $permission, string $ip): string
    {
        return "permission_check:{$userId}:{$permission}:{$ip}";
    }

    /**
     * Log rate limit exceeded
     */
    private function logRateLimitExceeded($user, string $permission, Request $request): void
    {
        \Log::warning('Permission rate limit exceeded', [
            'user_id' => $user->id,
            'permission' => $permission,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Store in database for analytics
        \DB::table('permission_rate_limits')->insert([
            'user_id' => $user->id,
            'permission' => $permission,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'exceeded_at' => now(),
        ]);
    }
}
```

---

### **COMMANDS** : Structure fournie pour les 5 commands

**Chaque command doit avoir** :
- ✅ Signature et description
- ✅ Constructor injection si besoin services
- ✅ Progress bar pour opérations longues
- ✅ Summary stats à la fin
- ✅ Logs appropriés

---

## ✅ CHECKLIST VALIDATION

Pour chaque fichier :

- [ ] PHPDoc complet
- [ ] Type hints partout
- [ ] Injection services si nécessaire
- [ ] Logs appropriés
- [ ] < 150 lignes
- [ ] Production-ready

---

## 🚀 COMMANDE

**Génère les 12 fichiers** :

**5 Observers** :
```
app/Observers/UserGroupObserver.php
app/Observers/PermissionTemplateObserver.php
app/Observers/PermissionGroupObserver.php
app/Observers/PermissionObserver.php
app/Observers/UserPermissionObserver.php
```

**1 Trait** :
```
app/Traits/HasPermissionsOptimized.php
```

**1 Middleware** :
```
app/Http/Middleware/PermissionRateLimiter.php
```

**5 Commands** :
```
app/Console/Commands/ExpirePermissionsCommand.php
app/Console/Commands/ExpireDelegationsCommand.php
app/Console/Commands/RebuildHierarchiesCommand.php
app/Console/Commands/WarmPermissionCacheCommand.php
app/Console/Commands/CleanupAuditLogCommand.php
```

---

**GO ! 🎯**
