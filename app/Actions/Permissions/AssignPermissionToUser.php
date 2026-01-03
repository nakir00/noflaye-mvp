<?php

namespace App\Actions\Permissions;

use App\Data\Permissions\AssignPermissionData;
use App\Enums\AuditAction;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Assign Permission to User
 *
 * This action handles the complete workflow for assigning a permission to a user,
 * including validation, idempotent behavior, audit logging, cache invalidation,
 * and performance metrics tracking.
 *
 * Features:
 * - Idempotent: Safe to retry without side effects
 * - Scoped: Supports both global and entity-scoped permissions
 * - Audited: Complete activity log with IP and user agent tracking
 * - Cached: Automatically invalidates relevant permission caches
 * - Metrics: Logs performance timing for monitoring
 *
 * @see AssignPermissionData The validated input DTO
 * @see Permission The permission model
 * @see User The user model
 */
class AssignPermissionToUser
{
    use AsAction;

    /**
     * Assign permission to user with full validation and audit trail
     *
     * This method performs the following steps:
     * 1. Validates user and permission existence
     * 2. Checks for existing assignment (idempotent behavior)
     * 3. Creates permission assignment with full metadata
     * 4. Logs activity with complete audit trail
     * 5. Invalidates affected permission caches
     * 6. Tracks performance metrics
     *
     * @param  AssignPermissionData  $data  The validated assignment data
     * @param  bool  $skipIfExists  If true, silently return true when permission exists (default: true)
     * @return bool True if permission was assigned or already exists, false on idempotent skip
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user or permission not found
     *
     * @example
     * $data = new AssignPermissionData(
     *     user_id: 123,
     *     permission: Permission::SHOP_VIEW,
     *     scope_id: 456
     * );
     * $assigned = AssignPermissionToUser::run($data);
     */
    public function handle(AssignPermissionData $data, bool $skipIfExists = true): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $skipIfExists, $startTime) {
                $user = User::findOrFail($data->user_id);
                $permission = Permission::where('slug', $data->permission->value)->firstOrFail();

                // Check if already assigned (idempotent behavior)
                $exists = $user->permissions()
                    ->where('permission_id', $permission->id)
                    ->where('scope_id', $data->scope_id)
                    ->exists();

                if ($exists) {
                    if ($skipIfExists) {
                        // Log idempotent skip for metrics
                        $this->logMetrics($startTime, 'skipped', $data);

                        return true; // Idempotent: already assigned
                    }

                    return false; // Non-idempotent: signal existing assignment
                }

                // Attach permission with full metadata
                $user->permissions()->attach($permission->id, [
                    'scope_id' => $data->scope_id,
                    'source' => $data->source,
                    'granted_at' => now(),
                    'valid_from' => $data->valid_from,
                    'valid_until' => $data->valid_until,
                    'reason' => $data->reason,
                ]);

                // Log activity with complete audit trail
                activity()
                    ->performedOn($user)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'permission' => $data->permission->value,
                        'permission_name' => $permission->name,
                        'scope_id' => $data->scope_id,
                        'source' => $data->source,
                        'reason' => $data->reason,
                        'valid_from' => $data->valid_from?->toDateTimeString(),
                        'valid_until' => $data->valid_until?->toDateTimeString(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log(AuditAction::GRANTED->value);

                // Clear permission caches for affected user
                Cache::tags(['users', "user.{$user->id}", 'permissions'])->flush();

                // Log success metrics
                $this->logMetrics($startTime, 'success', $data);

                return true;
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
     * Queued execution is useful for bulk operations or when assigning
     * permissions doesn't need to complete synchronously.
     *
     * @param  AssignPermissionData  $data  The validated assignment data
     * @param  bool  $skipIfExists  Idempotent behavior flag
     */
    public function asJob(AssignPermissionData $data, bool $skipIfExists = true): void
    {
        $this->handle($data, $skipIfExists);
    }

    /**
     * Log performance metrics for monitoring
     *
     * Tracks execution time and outcome for performance analysis
     * and alerting on permission assignment operations.
     *
     * @param  float  $startTime  Microtime when operation started
     * @param  string  $outcome  The operation outcome ('success', 'skipped', 'failed')
     * @param  AssignPermissionData  $data  The assignment data
     * @param  string|null  $error  Error message if failed
     */
    protected function logMetrics(float $startTime, string $outcome, AssignPermissionData $data, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        Log::info('Permission assignment', [
            'action' => 'assign_permission',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'user_id' => $data->user_id,
            'permission' => $data->permission->value,
            'scope_id' => $data->scope_id,
            'source' => $data->source,
            'error' => $error,
        ]);
    }
}
