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
 * Action: Create Supervisor
 *
 * Creates a new supervisor with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via SupervisorData
 * - Activity logging with audit trail
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $data = SupervisorData::from([
 *     'name' => 'Jane Smith',
 *     'slug' => 'jane-smith',
 *     'email' => 'jane@example.com',
 *     'phone' => '+221771234567',
 *     'address' => '123 Main St, Dakar',
 *     'is_active' => true,
 * ]);
 *
 * $supervisor = CreateSupervisor::run($data);
 * ```
 *
 * @see SupervisorData The validated input DTO
 * @see Supervisor The supervisor model
 */
class CreateSupervisor
{
    use AsAction;

    /**
     * Create a new supervisor
     *
     * @param  SupervisorData  $data  The validated supervisor data
     * @return Supervisor The created supervisor instance
     *
     * @throws \Exception If creation fails
     */
    public function handle(SupervisorData $data): Supervisor
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $startTime) {
                // Create supervisor
                $supervisor = Supervisor::create([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'phone' => $data->phone,
                    'email' => $data->email,
                    'address' => $data->address,
                    'is_active' => $data->is_active,
                ]);

                // Log activity
                activity()
                    ->performedOn($supervisor)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'name' => $supervisor->name,
                        'slug' => $supervisor->slug,
                        'email' => $supervisor->email,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('supervisor_created');

                // Invalidate caches
                Cache::tags(['supervisors'])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $supervisor);

                return $supervisor;
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', null, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  Supervisor|null  $supervisor  The created supervisor
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, ?Supervisor $supervisor, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Supervisor creation', [
            'action' => 'create_supervisor',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'supervisor_id' => $supervisor?->id,
            'supervisor_name' => $supervisor?->name,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  SupervisorData  $data  The supervisor data
     */
    public function asJob(SupervisorData $data): void
    {
        $this->handle($data);
    }
}
