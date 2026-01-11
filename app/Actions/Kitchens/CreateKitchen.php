<?php

namespace App\Actions\Kitchens;

use App\Data\Kitchens\KitchenData;
use App\Models\Kitchen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Create Kitchen
 *
 * Creates a new kitchen with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via KitchenData
 * - Activity logging with audit trail
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $data = KitchenData::from([
 *     'name' => 'Central Kitchen',
 *     'slug' => 'central-kitchen',
 *     'address' => '456 Industrial Ave, Dakar',
 *     'is_active' => true,
 * ]);
 *
 * $kitchen = CreateKitchen::run($data);
 * ```
 *
 * @see KitchenData The validated input DTO
 * @see Kitchen The kitchen model
 */
class CreateKitchen
{
    use AsAction;

    /**
     * Create a new kitchen
     *
     * @param  KitchenData  $data  The validated kitchen data
     * @return Kitchen The created kitchen instance
     *
     * @throws \Exception If creation fails
     */
    public function handle(KitchenData $data): Kitchen
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $startTime) {
                // Create kitchen
                $kitchen = Kitchen::create([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'address' => $data->address,
                    'is_active' => $data->is_active,
                ]);

                // Log activity
                activity()
                    ->performedOn($kitchen)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'name' => $kitchen->name,
                        'slug' => $kitchen->slug,
                        'address' => $kitchen->address,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('kitchen_created');

                // Invalidate caches
                Cache::tags(['kitchens'])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $kitchen);

                return $kitchen;
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
     * @param  Kitchen|null  $kitchen  The created kitchen
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, ?Kitchen $kitchen, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Kitchen creation', [
            'action' => 'create_kitchen',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'kitchen_id' => $kitchen?->id,
            'kitchen_name' => $kitchen?->name,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  KitchenData  $data  The kitchen data
     */
    public function asJob(KitchenData $data): void
    {
        $this->handle($data);
    }
}
