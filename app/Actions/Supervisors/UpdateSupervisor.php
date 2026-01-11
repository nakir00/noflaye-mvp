<?php

namespace App\Actions\Supervisors;

use App\Data\Supervisors\SupervisorData;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Update Supervisor
 *
 * Updates an existing supervisor with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via SupervisorData
 * - Change tracking (only modified fields)
 * - Activity logging with before/after values
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $supervisor = Supervisor::find(1);
 * $data = SupervisorData::from([
 *     'name' => 'Jane Updated',
 *     'slug' => 'jane-updated',
 *     'is_active' => false,
 * ]);
 *
 * $updated = UpdateSupervisor::run($supervisor, $data);
 * ```
 *
 * @see SupervisorData The validated input DTO
 * @see Supervisor The supervisor model
 */
class UpdateSupervisor
{
    use AsAction;

    /**
     * Update a supervisor
     *
     * @param  Supervisor  $supervisor  The supervisor to update
     * @param  SupervisorData  $data  The validated supervisor data
     * @return Supervisor The updated supervisor instance
     *
     * @throws \Exception If update fails
     */
    public function handle(Supervisor $supervisor, SupervisorData $data): Supervisor
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($supervisor, $data, $startTime) {
                // Track changes
                $original = $supervisor->getOriginal();

                // Update supervisor
                $supervisor->update([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'phone' => $data->phone,
                    'email' => $data->email,
                    'address' => $data->address,
                    'is_active' => $data->is_active,
                ]);

                // Get actual changes
                $changes = $supervisor->getChanges();

                // Log activity if there are changes
                if (! empty($changes)) {
                    activity()
                        ->performedOn($supervisor)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'changes' => $changes,
                            'old' => array_intersect_key($original, $changes),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ])
                        ->log('supervisor_updated');
                }

                // Invalidate caches
                Cache::tags(['supervisors', "supervisor.{$supervisor->id}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $supervisor, count($changes));

                return $supervisor->fresh();
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $supervisor, 0, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  Supervisor  $supervisor  The supervisor
     * @param  int  $changesCount  Number of changed fields
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, Supervisor $supervisor, int $changesCount, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Supervisor update', [
            'action' => 'update_supervisor',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'supervisor_id' => $supervisor->id,
            'supervisor_name' => $supervisor->name,
            'changes_count' => $changesCount,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Supervisor  $supervisor  The supervisor to update
     * @param  SupervisorData  $data  The supervisor data
     */
    public function asJob(Supervisor $supervisor, SupervisorData $data): void
    {
        $this->handle($supervisor, $data);
    }
}
