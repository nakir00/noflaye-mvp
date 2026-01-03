<?php

namespace App\Services\Permissions;

use App\Models\DelegationChain;
use App\Models\Permission;
use App\Models\PermissionDelegation;
use App\Models\Scope;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PermissionDelegator Service
 *
 * Manage permission delegation with re-delegation support
 *
 * @author Noflaye Box Team
 *
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
     * @param  User  $delegator  User delegating permission
     * @param  User  $delegatee  User receiving delegation
     * @param  Permission  $permission  Permission to delegate
     * @param  Carbon  $validUntil  Delegation expiration
     * @param  Scope|null  $scope  Scope context
     * @param  bool  $canRedelegate  Allow re-delegation
     * @param  int  $maxRedelegationDepth  Maximum re-delegation depth
     * @param  string|null  $reason  Reason for delegation
     *
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
        if (! $this->canDelegate($delegator, $permission, $scope)) {
            throw new \Exception("Delegator does not have permission to delegate: {$permission->slug}");
        }

        // Verify expiration is in future
        if ($validUntil->isPast()) {
            throw new \Exception('Delegation expiration must be in the future');
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
     * @param  PermissionDelegation  $delegation  Delegation to revoke
     * @param  User  $revokedBy  User revoking delegation
     * @param  string|null  $reason  Reason for revocation
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
     * @param  User  $user  User attempting to delegate
     * @param  Permission  $permission  Permission to check
     * @param  Scope|null  $scope  Scope context
     */
    public function canDelegate(User $user, Permission $permission, ?Scope $scope = null): bool
    {
        // User must have the permission themselves
        return $this->checker->checkWithScope($user, $permission->slug, $scope);
    }

    /**
     * Check re-delegation depth
     *
     * @param  PermissionDelegation  $delegation  Parent delegation
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
     * @param  PermissionDelegation  $delegation  Delegation to extend
     * @param  Carbon  $newExpiration  New expiration date
     *
     * @throws \Exception
     */
    public function extendDelegation(PermissionDelegation $delegation, Carbon $newExpiration): bool
    {
        if ($delegation->revoked_at) {
            throw new \Exception('Cannot extend revoked delegation');
        }

        if ($newExpiration->isPast()) {
            throw new \Exception('New expiration must be in the future');
        }

        if ($newExpiration->lessThan($delegation->valid_until)) {
            throw new \Exception('New expiration must be later than current expiration');
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
     * @param  User  $user  User to check
     * @param  Scope|null  $scope  Scope filter
     */
    public function getUserDelegations(User $user, ?Scope $scope = null): \Illuminate\Support\Collection
    {
        return PermissionDelegation::active()
            ->where('delegatee_id', $user->id)
            ->when($scope, fn ($q) => $q->where('scope_id', $scope->id))
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
