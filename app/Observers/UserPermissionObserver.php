<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Permission;
use App\Services\Permissions\PermissionAuditLogger;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Facades\Log;

/**
 * UserPermissionObserver
 *
 * Handle user-permission pivot events (attach/detach)
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class UserPermissionObserver
{
    public function __construct(
        private PermissionAuditLogger $auditLogger,
        private PermissionChecker $permissionChecker
    ) {}

    /**
     * Handle user permission attached event
     *
     * @param User $user
     * @param int $permissionId
     * @param array $pivotData
     */
    public function attached(User $user, int $permissionId, array $pivotData): void
    {
        $permission = Permission::find($permissionId);

        if (!$permission) {
            return;
        }

        // Get scope if present
        $scopeId = $pivotData['scope_id'] ?? null;
        $scope = $scopeId ? \App\Models\Scope::find($scopeId) : null;

        // Log grant
        $this->auditLogger->logGrant(
            $user,
            $permission,
            $scope,
            auth()->user() ?? $user,
            $pivotData['reason'] ?? null,
            $pivotData['source'] ?? 'direct'
        );

        // Invalidate user cache
        $this->permissionChecker->invalidateUserCache($user);

        Log::info('Permission granted to user', [
            'user_id' => $user->id,
            'permission_id' => $permissionId,
            'permission_slug' => $permission->slug,
            'scope_id' => $scopeId,
        ]);
    }

    /**
     * Handle user permission detached event
     *
     * @param User $user
     * @param int $permissionId
     */
    public function detached(User $user, int $permissionId): void
    {
        $permission = Permission::find($permissionId);

        if (!$permission) {
            return;
        }

        // Log revoke
        $this->auditLogger->logRevoke(
            $user,
            $permission,
            auth()->user() ?? $user
        );

        // Invalidate user cache
        $this->permissionChecker->invalidateUserCache($user);

        Log::info('Permission revoked from user', [
            'user_id' => $user->id,
            'permission_id' => $permissionId,
            'permission_slug' => $permission->slug,
        ]);
    }

    /**
     * Handle user permission synced event
     *
     * @param User $user
     * @param array $changes
     */
    public function synced(User $user, array $changes): void
    {
        // Invalidate user cache on sync
        $this->permissionChecker->invalidateUserCache($user);

        Log::info('User permissions synced', [
            'user_id' => $user->id,
            'attached' => count($changes['attached'] ?? []),
            'detached' => count($changes['detached'] ?? []),
            'updated' => count($changes['updated'] ?? []),
        ]);
    }
}
