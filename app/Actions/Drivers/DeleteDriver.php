<?php

namespace App\Actions\Drivers;

use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Delete Driver
 *
 * Deletes a driver with audit logging and cache management.
 *
 * Features:
 * - Soft delete support (if enabled on model)
 * - Activity logging with driver details
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $driver = Driver::find(1);
 * $deleted = DeleteDriver::run($driver);
 * ```
 *
 * @see Driver The driver model
 */
class DeleteDriver
{
    use AsAction;

    /**
     * Delete a driver
     *
     * @param  Driver  $driver  The driver to delete
     * @param  bool  $force  Whether to force delete (skip soft delete)
     * @return bool True if deletion was successful
     *
     * @throws \Exception If deletion fails
     */
    public function handle(Driver $driver, bool $force = false): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($driver, $force, $startTime) {
                // Store driver info before deletion
                $driverId = $driver->id;
                $driverName = $driver->name;
                $driverData = [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'slug' => $driver->slug,
                    'email' => $driver->email,
                    'vehicle_type' => $driver->vehicle_type,
                    'vehicle_number' => $driver->vehicle_number,
                ];

                // Log activity before deletion
                activity()
                    ->performedOn($driver)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'driver_data' => $driverData,
                        'force_delete' => $force,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('driver_deleted');

                // Delete driver
                $deleted = $force ? $driver->forceDelete() : $driver->delete();

                // Invalidate caches
                Cache::tags(['drivers', "driver.{$driverId}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $driverId, $driverName, $force);

                return $deleted;
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $driver->id, $driver->name, $force, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  int  $driverId  The driver ID
     * @param  string  $driverName  The driver name
     * @param  bool  $force  Whether it was a force delete
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, int $driverId, string $driverName, bool $force, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Driver deletion', [
            'action' => 'delete_driver',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'driver_id' => $driverId,
            'driver_name' => $driverName,
            'force_delete' => $force,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Driver  $driver  The driver to delete
     * @param  bool  $force  Whether to force delete
     */
    public function asJob(Driver $driver, bool $force = false): void
    {
        $this->handle($driver, $force);
    }
}
