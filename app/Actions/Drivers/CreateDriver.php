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
 * Action: Create Driver
 *
 * Creates a new driver with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via DriverData
 * - Activity logging with audit trail
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $data = DriverData::from([
 *     'name' => 'John Doe',
 *     'slug' => 'john-doe',
 *     'email' => 'john@example.com',
 *     'phone' => '+221771234567',
 *     'vehicle_type' => 'Van',
 *     'vehicle_number' => 'DK-1234-AB',
 *     'license_number' => 'B123456',
 *     'is_active' => true,
 *     'is_available' => true,
 * ]);
 *
 * $driver = CreateDriver::run($data);
 * ```
 *
 * @see DriverData The validated input DTO
 * @see Driver The driver model
 */
class CreateDriver
{
    use AsAction;

    /**
     * Create a new driver
     *
     * @param  DriverData  $data  The validated driver data
     * @return Driver The created driver instance
     *
     * @throws \Exception If creation fails
     */
    public function handle(DriverData $data): Driver
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $startTime) {
                // Create driver
                $driver = Driver::create([
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

                // Log activity
                activity()
                    ->performedOn($driver)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'name' => $driver->name,
                        'slug' => $driver->slug,
                        'vehicle_type' => $driver->vehicle_type,
                        'vehicle_number' => $driver->vehicle_number,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('driver_created');

                // Invalidate caches
                Cache::tags(['drivers'])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $driver);

                return $driver;
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
     * @param  Driver|null  $driver  The created driver
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, ?Driver $driver, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Driver creation', [
            'action' => 'create_driver',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'driver_id' => $driver?->id,
            'driver_name' => $driver?->name,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  DriverData  $data  The driver data
     */
    public function asJob(DriverData $data): void
    {
        $this->handle($data);
    }
}
