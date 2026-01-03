<?php

namespace App\Actions\Permissions;

use App\Data\Permissions\RevokePermissionData;
use App\Enums\AuditAction;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Revoke Permission from User
 *
 * This action handles the complete workflow for revoking a permission from a user,
 * including validation, idempotent behavior, audit logging, cache invalidation,
 * and performance metrics tracking.
 *
 * Features:
 * - Idempotent: Safe to retry without side effects
 * - Scoped: Supports both global and entity-scoped permission revocation
 * - Audited: Complete activity log with IP and user agent tracking
 * - Cached: Automatically invalidates relevant permission caches
 * - Metrics: Logs performance timing for monitoring
 *
 * @see RevokePermissionData The validated input DTO
 * @see Permission The permission model
 * @see User The user model
 */
class RevokePermissionFromUser
{
    use AsAction;

    /**
     * Revoke permission from user with full validation and audit trail
     *
     * This method performs the following steps:
     * 1. Validates user and permission existence
     * 2. Removes permission assignment (idempotent if not assigned)
     * 3. Logs activity with complete audit trail (only if revoked)
     * 4. Invalidates affected permission caches
     * 5. Tracks performance metrics
     *
     * @param  RevokePermissionData  $data  The validated revocation data
     * @param  bool  $skipIfNotExists  If true, silently return true when permission doesn't exist (default: true)
     * @return bool True if permission was revoked or doesn't exist, false if no permission to revoke
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user or permission not found
     *
     * @example
     * $data = new RevokePermissionData(
     *     user_id: 123,
     *     permission: Permission::SHOP_VIEW,
     *     scope_id: 456,
     *     reason: 'Employee terminated'
     * );
     * $revoked = RevokePermissionFromUser::run($data);
     */
    public function handle(RevokePermissionData $data, bool $skipIfNotExists = true): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $skipIfNotExists, $startTime) {
                $user = User::findOrFail($data->user_id);
                $permission = Permission::where('slug', $data->permission->value)->firstOrFail();

                // Attempt to detach permission
                $detached = $user->permissions()
                    ->wherePivot('permission_id', $permission->id)
                    ->wherePivot('scope_id', $data->scope_id)
                    ->detach();

                if ($detached > 0) {
                    // Log activity with complete audit trail
                    activity()
                        ->performedOn($user)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'permission' => $data->permission->value,
                            'permission_name' => $permission->name,
                            'scope_id' => $data->scope_id,
                            'reason' => $data->reason,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ])
                        ->log(AuditAction::REVOKED->value);

                    // Clear permission caches for affected user
                    Cache::tags(['users', "user.{$user->id}", 'permissions'])->flush();

                    // Log success metrics
                    $this->logMetrics($startTime, 'success', $data);

                    return true;
                }

                // No permission was revoked (idempotent case)
                if ($skipIfNotExists) {
                    $this->logMetrics($startTime, 'skipped', $data);

                    return true; // Idempotent: already revoked or never assigned
                }

                $this->logMetrics($startTime, 'not_found', $data);

                return false; // Non-idempotent: signal no permission to revoke
            });
        } catch (\Exception $e) {
            // Log failure metrics
            $this->logMetrics($startTime, 'failed', $data, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Execute action as a queued job
     *
     * Queued execution is useful for bulk operations or when revoking
     * permissions doesn't need to complete synchronously.
     *
     * @param  RevokePermissionData  $data  The validated revocation data
     * @param  bool  $skipIfNotExists  Idempotent behavior flag
     */
    public function asJob(RevokePermissionData $data, bool $skipIfNotExists = true): void
    {
        $this->handle($data, $skipIfNotExists);
    }

    /**
     * Log performance metrics for monitoring
     *
     * Tracks execution time and outcome for performance analysis
     * and alerting on permission revocation operations.
     *
     * @param  float  $startTime  Microtime when operation started
     * @param  string  $outcome  The operation outcome ('success', 'skipped', 'not_found', 'failed')
     * @param  RevokePermissionData  $data  The revocation data
     * @param  string|null  $error  Error message if failed
     */
    protected function logMetrics(float $startTime, string $outcome, RevokePermissionData $data, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        Log::info('Permission revocation', [
            'action' => 'revoke_permission',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'user_id' => $data->user_id,
            'permission' => $data->permission->value,
            'scope_id' => $data->scope_id,
            'reason' => $data->reason,
            'error' => $error,
        ]);
    }
}
