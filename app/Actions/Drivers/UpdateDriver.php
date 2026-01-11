<?php

namespace App\Actions\Drivers;

use App\Data\Drivers\DriverData;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Update Driver
 *
 * Updates an existing driver with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via DriverData
 * - Change tracking (only modified fields)
 * - Activity logging with before/after values
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $driver = Driver::find(1);
 * $data = DriverData::from([
 *     'name' => 'John Updated',
 *     'slug' => 'john-updated',
 *     'is_available' => false,
 * ]);
 *
 * $updated = UpdateDriver::run($driver, $data);
 * ```
 *
 * @see DriverData The validated input DTO
 * @see Driver The driver model
 */
class UpdateDriver
{
    use AsAction;

    /**
     * Update a driver
     *
     * @param  Driver  $driver  The driver to update
     * @param  DriverData  $data  The validated driver data
     * @return Driver The updated driver instance
     *
     * @throws \Exception If update fails
     */
    public function handle(Driver $driver, DriverData $data): Driver
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($driver, $data, $startTime) {
                // Track changes
                $changes = [];
                $original = $driver->getOriginal();

                // Update driver
                $driver->update([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'phone' => $data->phone,
                    'email' => $data->email,
                    'vehicle_type' => $data->vehicle_type,
                    'vehicle_number' => $data->vehicle_number,
                    'license_number' => $data->license_number,
                    'is_active' => $data->is_active,
                    'is_available' => $data->is_available,
                ]);

                // Get actual changes
                $changes = $driver->getChanges();

                // Log activity if there are changes
                if (! empty($changes)) {
                    activity()
                        ->performedOn($driver)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'changes' => $changes,
                            'old' => array_intersect_key($original, $changes),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ])
                        ->log('driver_updated');
                }

                // Invalidate caches
                Cache::tags(['drivers', "driver.{$driver->id}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $driver, count($changes));

                return $driver->fresh();
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $driver, 0, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  Driver  $driver  The driver
     * @param  int  $changesCount  Number of changed fields
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, Driver $driver, int $changesCount, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Driver update', [
            'action' => 'update_driver',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'driver_id' => $driver->id,
            'driver_name' => $driver->name,
            'changes_count' => $changesCount,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Driver  $driver  The driver to update
     * @param  DriverData  $data  The driver data
     */
    public function asJob(Driver $driver, DriverData $data): void
    {
        $this->handle($driver, $data);
    }
}
