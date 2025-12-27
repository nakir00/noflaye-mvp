# 🚀 PROMPT CLAUDE CODE - PARTIE 7 : SERVICES ADVANCED

> **Contexte** : Créer services avancés pour audit, délégation, versioning, analytics, et workflow

---

## 📋 OBJECTIF

Créer **5 fichiers de services avancés** pour implémenter la logique métier complexe du système de permissions.

**Principe** : Services avec injection de dépendances, logging complet, et gestion workflow.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture**
- ✅ Injection de dépendances via constructeur
- ✅ Type hints partout (paramètres + return types)
- ✅ Utilisation Services Core (PermissionChecker, etc.)
- ✅ Events Laravel pour notifications

### **Performance**
- ✅ Bulk operations pour analytics
- ✅ Eager loading explicite
- ✅ Query optimization
- ✅ Cache quand approprié

### **Code Quality**
- ✅ PHPDoc exhaustif avec @throws
- ✅ Validation inputs
- ✅ Logs pour toutes actions
- ✅ < 300 lignes par fichier

---

## 📁 LISTE DES 5 SERVICES

```
app/Services/Permissions/PermissionAuditLogger.php
app/Services/Permissions/PermissionDelegator.php
app/Services/Permissions/TemplateVersionManager.php
app/Services/Permissions/PermissionAnalytics.php
app/Services/Permissions/PermissionApprovalWorkflow.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **SERVICE 1 : PermissionAuditLogger**

**Fichier** : `app/Services/Permissions/PermissionAuditLogger.php`

**Purpose** : Logging complet de toutes actions sur permissions

```php
<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\Permission;
use App\Models\Scope;
use App\Models\PermissionTemplate;
use App\Models\PermissionDelegation;
use App\Models\PermissionRequest;
use App\Models\PermissionAuditLog;
use App\Enums\AuditAction;
use Illuminate\Support\Facades\Log;

/**
 * PermissionAuditLogger Service
 *
 * Comprehensive audit logging for all permission changes
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionAuditLogger
{
    /**
     * Log permission grant
     *
     * @param User $user User receiving permission
     * @param Permission $permission Permission granted
     * @param Scope|null $scope Scope context
     * @param User $performedBy User granting permission
     * @param string|null $reason Reason for grant
     * @param string $source Source (direct, template, wildcard, inherited)
     * @param int|null $sourceId Source ID (template_id, wildcard_id, etc.)
     * @return PermissionAuditLog
     */
    public function logGrant(
        User $user,
        Permission $permission,
        ?Scope $scope,
        User $performedBy,
        ?string $reason = null,
        string $source = 'direct',
        ?int $sourceId = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::GRANTED->value,
            'permission_slug' => $permission->slug,
            'permission_name' => $permission->name,
            'source' => $source,
            'source_id' => $sourceId,
            'source_name' => $this->getSourceName($source, $sourceId),
            'scope_id' => $scope?->id,
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'metadata' => [
                'permission_group' => $permission->group?->name,
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log permission revoke
     *
     * @param User $user User losing permission
     * @param Permission $permission Permission revoked
     * @param User $performedBy User revoking permission
     * @param string|null $reason Reason for revoke
     * @return PermissionAuditLog
     */
    public function logRevoke(
        User $user,
        Permission $permission,
        User $performedBy,
        ?string $reason = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::REVOKED->value,
            'permission_slug' => $permission->slug,
            'permission_name' => $permission->name,
            'source' => 'direct',
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log template assignment
     *
     * @param User $user User receiving template
     * @param PermissionTemplate $template Template assigned
     * @param User $performedBy User assigning template
     * @param Scope|null $scope Scope context
     * @param string|null $reason Reason for assignment
     * @return PermissionAuditLog
     */
    public function logTemplateAssignment(
        User $user,
        PermissionTemplate $template,
        User $performedBy,
        ?Scope $scope = null,
        ?string $reason = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::TEMPLATE_ASSIGNED->value,
            'permission_slug' => 'template.' . $template->slug,
            'permission_name' => $template->name,
            'source' => 'template',
            'source_id' => $template->id,
            'source_name' => $template->name,
            'scope_id' => $scope?->id,
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'metadata' => [
                'template_permissions_count' => $template->permissions->count(),
                'template_wildcards_count' => $template->wildcards->count(),
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log template removal
     *
     * @param User $user User losing template
     * @param PermissionTemplate $template Template removed
     * @param User $performedBy User removing template
     * @param string|null $reason Reason for removal
     * @return PermissionAuditLog
     */
    public function logTemplateRemoval(
        User $user,
        PermissionTemplate $template,
        User $performedBy,
        ?string $reason = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::TEMPLATE_REMOVED->value,
            'permission_slug' => 'template.' . $template->slug,
            'permission_name' => $template->name,
            'source' => 'template',
            'source_id' => $template->id,
            'source_name' => $template->name,
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log delegation creation
     *
     * @param PermissionDelegation $delegation Delegation created
     * @return PermissionAuditLog
     */
    public function logDelegation(PermissionDelegation $delegation): PermissionAuditLog
    {
        return $this->createLog([
            'user_id' => $delegation->delegatee_id,
            'user_name' => $delegation->delegatee_name,
            'user_email' => null,
            'action' => AuditAction::DELEGATED->value,
            'permission_slug' => $delegation->permission_slug,
            'permission_name' => null,
            'source' => 'delegation',
            'source_id' => $delegation->id,
            'source_name' => 'Delegation from ' . $delegation->delegator_name,
            'scope_id' => $delegation->scope_id,
            'performed_by' => $delegation->delegator_id,
            'performed_by_name' => $delegation->delegator_name,
            'reason' => $delegation->reason,
            'metadata' => [
                'valid_until' => $delegation->valid_until->toDateTimeString(),
                'can_redelegate' => $delegation->can_redelegate,
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log permission request
     *
     * @param PermissionRequest $request Request created/updated
     * @param string $action Action (requested, approved, rejected)
     * @return PermissionAuditLog
     */
    public function logRequest(PermissionRequest $request, string $action): PermissionAuditLog
    {
        $auditAction = match($action) {
            'requested' => AuditAction::REQUESTED,
            'approved' => AuditAction::REQUEST_APPROVED,
            'rejected' => AuditAction::REQUEST_REJECTED,
            default => AuditAction::UPDATED,
        };

        return $this->createLog([
            'user_id' => $request->user_id,
            'user_name' => $request->user->name,
            'user_email' => $request->user->email,
            'action' => $auditAction->value,
            'permission_slug' => $request->permission->slug,
            'permission_name' => $request->permission->name,
            'source' => 'request',
            'source_id' => $request->id,
            'scope_id' => $request->scope_id,
            'performed_by' => $request->reviewed_by,
            'performed_by_name' => $request->reviewer?->name,
            'reason' => $request->reason,
            'metadata' => [
                'status' => $request->status,
                'review_comment' => $request->review_comment,
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Create audit log entry
     *
     * @param array $data
     * @return PermissionAuditLog
     */
    private function createLog(array $data): PermissionAuditLog
    {
        $log = PermissionAuditLog::create($data);

        Log::info('Permission audit log created', [
            'log_id' => $log->id,
            'action' => $data['action'],
            'user_id' => $data['user_id'],
            'permission_slug' => $data['permission_slug'],
        ]);

        return $log;
    }

    /**
     * Get source name from source type and ID
     *
     * @param string $source
     * @param int|null $sourceId
     * @return string|null
     */
    private function getSourceName(string $source, ?int $sourceId): ?string
    {
        if (!$sourceId) {
            return null;
        }

        return match($source) {
            'template' => PermissionTemplate::find($sourceId)?->name,
            'wildcard' => 'Wildcard #' . $sourceId,
            default => null,
        };
    }
}
```

---

### **SERVICE 2 : PermissionDelegator**

**Fichier** : `app/Services/Permissions/PermissionDelegator.php`

```php
<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\Permission;
use App\Models\Scope;
use App\Models\PermissionDelegation;
use App\Models\DelegationChain;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PermissionDelegator Service
 *
 * Manage permission delegation with re-delegation support
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionDelegator
{
    public function __construct(
        private PermissionChecker $checker,
        private PermissionAuditLogger $auditLogger
    ) {}

    /**
     * Delegate permission to another user
     *
     * @param User $delegator User delegating permission
     * @param User $delegatee User receiving delegation
     * @param Permission $permission Permission to delegate
     * @param Carbon $validUntil Delegation expiration
     * @param Scope|null $scope Scope context
     * @param bool $canRedelegate Allow re-delegation
     * @param int $maxRedelegationDepth Maximum re-delegation depth
     * @param string|null $reason Reason for delegation
     * @return PermissionDelegation
     * @throws \Exception
     */
    public function delegate(
        User $delegator,
        User $delegatee,
        Permission $permission,
        Carbon $validUntil,
        ?Scope $scope = null,
        bool $canRedelegate = false,
        int $maxRedelegationDepth = 0,
        ?string $reason = null
    ): PermissionDelegation {
        // Verify delegator has permission
        if (!$this->canDelegate($delegator, $permission, $scope)) {
            throw new \Exception("Delegator does not have permission to delegate: {$permission->slug}");
        }

        // Verify expiration is in future
        if ($validUntil->isPast()) {
            throw new \Exception("Delegation expiration must be in the future");
        }

        return DB::transaction(function () use (
            $delegator,
            $delegatee,
            $permission,
            $validUntil,
            $scope,
            $canRedelegate,
            $maxRedelegationDepth,
            $reason
        ) {
            // Create delegation
            $delegation = PermissionDelegation::create([
                'delegator_id' => $delegator->id,
                'delegator_name' => $delegator->name,
                'delegatee_id' => $delegatee->id,
                'delegatee_name' => $delegatee->name,
                'permission_id' => $permission->id,
                'permission_slug' => $permission->slug,
                'scope_id' => $scope?->id,
                'valid_from' => now(),
                'valid_until' => $validUntil,
                'can_redelegate' => $canRedelegate,
                'max_redelegation_depth' => $maxRedelegationDepth,
                'reason' => $reason,
            ]);

            // Log delegation
            $this->auditLogger->logDelegation($delegation);

            Log::info('Permission delegated', [
                'delegation_id' => $delegation->id,
                'delegator_id' => $delegator->id,
                'delegatee_id' => $delegatee->id,
                'permission_slug' => $permission->slug,
                'valid_until' => $validUntil->toDateTimeString(),
            ]);

            return $delegation;
        });
    }

    /**
     * Revoke delegation
     *
     * @param PermissionDelegation $delegation Delegation to revoke
     * @param User $revokedBy User revoking delegation
     * @param string|null $reason Reason for revocation
     * @return bool
     */
    public function revoke(
        PermissionDelegation $delegation,
        User $revokedBy,
        ?string $reason = null
    ): bool {
        if ($delegation->revoked_at) {
            return false; // Already revoked
        }

        $result = $delegation->revoke($revokedBy, $reason);

        if ($result) {
            Log::info('Delegation revoked', [
                'delegation_id' => $delegation->id,
                'revoked_by' => $revokedBy->id,
                'reason' => $reason,
            ]);
        }

        return $result;
    }

    /**
     * Check if user can delegate permission
     *
     * @param User $user User attempting to delegate
     * @param Permission $permission Permission to check
     * @param Scope|null $scope Scope context
     * @return bool
     */
    public function canDelegate(User $user, Permission $permission, ?Scope $scope = null): bool
    {
        // User must have the permission themselves
        return $this->checker->checkWithScope($user, $permission->slug, $scope);
    }

    /**
     * Check re-delegation depth
     *
     * @param PermissionDelegation $delegation Parent delegation
     * @return int Current depth
     */
    public function checkRedelegationDepth(PermissionDelegation $delegation): int
    {
        $depth = DelegationChain::where('delegation_id', $delegation->id)
            ->max('depth');

        return $depth ?? 0;
    }

    /**
     * Extend delegation expiration
     *
     * @param PermissionDelegation $delegation Delegation to extend
     * @param Carbon $newExpiration New expiration date
     * @return bool
     * @throws \Exception
     */
    public function extendDelegation(PermissionDelegation $delegation, Carbon $newExpiration): bool
    {
        if ($delegation->revoked_at) {
            throw new \Exception("Cannot extend revoked delegation");
        }

        if ($newExpiration->isPast()) {
            throw new \Exception("New expiration must be in the future");
        }

        if ($newExpiration->lessThan($delegation->valid_until)) {
            throw new \Exception("New expiration must be later than current expiration");
        }

        $result = $delegation->update([
            'valid_until' => $newExpiration,
        ]);

        if ($result) {
            Log::info('Delegation extended', [
                'delegation_id' => $delegation->id,
                'old_expiration' => $delegation->getOriginal('valid_until'),
                'new_expiration' => $newExpiration->toDateTimeString(),
            ]);
        }

        return $result;
    }

    /**
     * Get all active delegations for user
     *
     * @param User $user User to check
     * @param Scope|null $scope Scope filter
     * @return \Illuminate\Support\Collection
     */
    public function getUserDelegations(User $user, ?Scope $scope = null): \Illuminate\Support\Collection
    {
        return PermissionDelegation::active()
            ->where('delegatee_id', $user->id)
            ->when($scope, fn($q) => $q->where('scope_id', $scope->id))
            ->with(['permission', 'delegator', 'scope'])
            ->get();
    }

    /**
     * Expire all delegations
     *
     * @return int Number of expired delegations
     */
    public function expireExpiredDelegations(): int
    {
        $expired = PermissionDelegation::whereNull('revoked_at')
            ->where('valid_until', '<=', now())
            ->get();

        foreach ($expired as $delegation) {
            // Don't actually update, just log
            Log::info('Delegation auto-expired', [
                'delegation_id' => $delegation->id,
                'delegatee_id' => $delegation->delegatee_id,
                'permission_slug' => $delegation->permission_slug,
            ]);
        }

        return $expired->count();
    }
}
```

---

### **SERVICE 3 : TemplateVersionManager**

**Fichier** : `app/Services/Permissions/TemplateVersionManager.php`

```php
<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\PermissionTemplate;
use App\Models\PermissionTemplateVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TemplateVersionManager Service
 *
 * Manage permission template versioning and publishing
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class TemplateVersionManager
{
    /**
     * Create new version snapshot
     *
     * @param PermissionTemplate $template Template to version
     * @param User $user User creating version
     * @param string|null $versionName Optional version name
     * @param string|null $changelog Optional changelog
     * @return PermissionTemplateVersion
     */
    public function createVersion(
        PermissionTemplate $template,
        User $user,
        ?string $versionName = null,
        ?string $changelog = null
    ): PermissionTemplateVersion {
        return DB::transaction(function () use ($template, $user, $versionName, $changelog) {
            // Get next version number
            $latestVersion = PermissionTemplateVersion::where('template_id', $template->id)
                ->max('version');
            $nextVersion = ($latestVersion ?? 0) + 1;

            // Create snapshots
            $permissionsSnapshot = $template->permissions->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'slug' => $perm->slug,
                    'name' => $perm->name,
                    'source' => $perm->pivot->source,
                ];
            })->toArray();

            $wildcardsSnapshot = $template->wildcards->map(function ($wildcard) {
                return [
                    'id' => $wildcard->id,
                    'pattern' => $wildcard->pattern,
                    'description' => $wildcard->description,
                ];
            })->toArray();

            // Create version
            $version = PermissionTemplateVersion::create([
                'template_id' => $template->id,
                'version' => $nextVersion,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'parent_id' => $template->parent_id,
                'scope_id' => $template->scope_id,
                'color' => $template->color,
                'icon' => $template->icon,
                'level' => $template->level,
                'permissions_snapshot' => $permissionsSnapshot,
                'wildcards_snapshot' => $wildcardsSnapshot,
                'version_name' => $versionName,
                'changelog' => $changelog,
                'is_stable' => false,
                'is_published' => false,
                'created_by' => $user->id,
            ]);

            Log::info('Template version created', [
                'version_id' => $version->id,
                'template_id' => $template->id,
                'version' => $nextVersion,
                'created_by' => $user->id,
            ]);

            return $version;
        });
    }

    /**
     * Publish version
     *
     * @param PermissionTemplateVersion $version Version to publish
     * @param User $user User publishing version
     * @return bool
     */
    public function publish(PermissionTemplateVersion $version, User $user): bool
    {
        if ($version->is_published) {
            return false; // Already published
        }

        $result = $version->update([
            'is_published' => true,
            'is_stable' => true,
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        if ($result) {
            Log::info('Template version published', [
                'version_id' => $version->id,
                'template_id' => $version->template_id,
                'version' => $version->version,
                'published_by' => $user->id,
            ]);
        }

        return $result;
    }

    /**
     * Restore template to specific version
     *
     * @param PermissionTemplateVersion $version Version to restore
     * @return bool
     */
    public function restore(PermissionTemplateVersion $version): bool
    {
        return DB::transaction(function () use ($version) {
            $template = $version->template;

            // Restore template metadata
            $template->update([
                'name' => $version->name,
                'slug' => $version->slug,
                'description' => $version->description,
                'parent_id' => $version->parent_id,
                'scope_id' => $version->scope_id,
                'color' => $version->color,
                'icon' => $version->icon,
            ]);

            // Restore permissions
            $template->permissions()->detach();
            foreach ($version->permissions_snapshot as $perm) {
                $template->permissions()->attach($perm['id'], [
                    'source' => $perm['source'],
                ]);
            }

            // Restore wildcards
            $template->wildcards()->detach();
            foreach ($version->wildcards_snapshot as $wildcard) {
                $template->wildcards()->attach($wildcard['id']);
            }

            Log::info('Template restored to version', [
                'template_id' => $template->id,
                'version_id' => $version->id,
                'version' => $version->version,
            ]);

            return true;
        });
    }

    /**
     * Compare two versions
     *
     * @param PermissionTemplateVersion $v1 First version
     * @param PermissionTemplateVersion $v2 Second version
     * @return array Diff result
     */
    public function compareVersions(PermissionTemplateVersion $v1, PermissionTemplateVersion $v2): array
    {
        $v1Perms = collect($v1->permissions_snapshot)->pluck('slug')->sort()->values();
        $v2Perms = collect($v2->permissions_snapshot)->pluck('slug')->sort()->values();

        $added = $v2Perms->diff($v1Perms);
        $removed = $v1Perms->diff($v2Perms);

        return [
            'version_1' => $v1->version,
            'version_2' => $v2->version,
            'permissions_added' => $added->values()->toArray(),
            'permissions_removed' => $removed->values()->toArray(),
            'permissions_count_change' => $v2Perms->count() - $v1Perms->count(),
        ];
    }

    /**
     * Rollback template to previous version
     *
     * @param PermissionTemplate $template Template to rollback
     * @param int $steps Number of versions to go back
     * @return PermissionTemplateVersion|null
     */
    public function rollback(PermissionTemplate $template, int $steps = 1): ?PermissionTemplateVersion
    {
        $currentVersion = PermissionTemplateVersion::where('template_id', $template->id)
            ->latest('version')
            ->first();

        if (!$currentVersion) {
            return null;
        }

        $targetVersion = $currentVersion->version - $steps;

        if ($targetVersion < 1) {
            return null;
        }

        $version = PermissionTemplateVersion::where('template_id', $template->id)
            ->where('version', $targetVersion)
            ->first();

        if ($version) {
            $this->restore($version);
        }

        return $version;
    }
}
```

---

### **SERVICES 4-5** : Structure similaire fournie

**PermissionAnalytics.php** (~250 lignes) - Analytics et statistiques
**PermissionApprovalWorkflow.php** (~200 lignes) - Workflow approbation

---

## ✅ CHECKLIST VALIDATION

Pour chaque service :

- [ ] PHPDoc complet
- [ ] Constructor injection
- [ ] Type hints complets
- [ ] Validation inputs
- [ ] Transactions DB si multi-operations
- [ ] Logs pour actions critiques
- [ ] < 300 lignes

---

## 🚀 COMMANDE

**Génère les 5 fichiers dans :**
```
app/Services/Permissions/
```

**Tous les fichiers doivent :**
1. Utiliser Services Core (injection)
2. Logger toutes actions critiques
3. Transactions pour multi-operations
4. Type hints complets
5. Être production-ready

---

**GO ! 🎯**
