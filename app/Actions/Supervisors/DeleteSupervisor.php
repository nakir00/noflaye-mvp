<?php

namespace App\Actions\Supervisors;

use App\Models\Supervisor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Delete Supervisor
 *
 * Deletes a supervisor with audit logging and cache management.
 *
 * Features:
 * - Soft delete support (if enabled on model)
 * - Activity logging with supervisor details
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $supervisor = Supervisor::find(1);
 * $deleted = DeleteSupervisor::run($supervisor);
 * ```
 *
 * @see Supervisor The supervisor model
 */
class DeleteSupervisor
{
    use AsAction;

    /**
     * Delete a supervisor
     *
     * @param  Supervisor  $supervisor  The supervisor to delete
     * @param  bool  $force  Whether to force delete (skip soft delete)
     * @return bool True if deletion was successful
     *
     * @throws \Exception If deletion fails
     */
    public function handle(Supervisor $supervisor, bool $force = false): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($supervisor, $force, $startTime) {
                // Store supervisor info before deletion
                $supervisorId = $supervisor->id;
                $supervisorName = $supervisor->name;
                $supervisorData = [
                    'id' => $supervisor->id,
                    'name' => $supervisor->name,
                    'slug' => $supervisor->slug,
                    'email' => $supervisor->email,
                    'phone' => $supervisor->phone,
                ];

                // Log activity before deletion
                activity()
                    ->performedOn($supervisor)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'supervisor_data' => $supervisorData,
                        'force_delete' => $force,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('supervisor_deleted');

                // Delete supervisor
                $deleted = $force ? $supervisor->forceDelete() : $supervisor->delete();

                // Invalidate caches
                Cache::tags(['supervisors', "supervisor.{$supervisorId}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $supervisorId, $supervisorName, $force);

                return $deleted;
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $supervisor->id, $supervisor->name, $force, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  int  $supervisorId  The supervisor ID
     * @param  string  $supervisorName  The supervisor name
     * @param  bool  $force  Whether it was a force delete
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, int $supervisorId, string $supervisorName, bool $force, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Supervisor deletion', [
            'action' => 'delete_supervisor',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'supervisor_id' => $supervisorId,
            'supervisor_name' => $supervisorName,
            'force_delete' => $force,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Supervisor  $supervisor  The supervisor to delete
     * @param  bool  $force  Whether to force delete
     */
    public function asJob(Supervisor $supervisor, bool $force = false): void
    {
        $this->handle($supervisor, $force);
    }
}
