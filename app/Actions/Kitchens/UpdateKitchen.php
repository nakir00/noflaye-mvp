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
 * Action: Update Kitchen
 *
 * Updates an existing kitchen with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via KitchenData
 * - Change tracking (only modified fields)
 * - Activity logging with before/after values
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $kitchen = Kitchen::find(1);
 * $data = KitchenData::from([
 *     'name' => 'Central Kitchen Updated',
 *     'slug' => 'central-kitchen-updated',
 *     'is_active' => false,
 * ]);
 *
 * $updated = UpdateKitchen::run($kitchen, $data);
 * ```
 *
 * @see KitchenData The validated input DTO
 * @see Kitchen The kitchen model
 */
class UpdateKitchen
{
    use AsAction;

    /**
     * Update a kitchen
     *
     * @param  Kitchen  $kitchen  The kitchen to update
     * @param  KitchenData  $data  The validated kitchen data
     * @return Kitchen The updated kitchen instance
     *
     * @throws \Exception If update fails
     */
    public function handle(Kitchen $kitchen, KitchenData $data): Kitchen
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($kitchen, $data, $startTime) {
                // Track changes
                $original = $kitchen->getOriginal();

                // Update kitchen
                $kitchen->update([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'address' => $data->address,
                    'is_active' => $data->is_active,
                ]);

                // Get actual changes
                $changes = $kitchen->getChanges();

                // Log activity if there are changes
                if (! empty($changes)) {
                    activity()
                        ->performedOn($kitchen)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'changes' => $changes,
                            'old' => array_intersect_key($original, $changes),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ])
                        ->log('kitchen_updated');
                }

                // Invalidate caches
                Cache::tags(['kitchens', "kitchen.{$kitchen->id}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $kitchen, count($changes));

                return $kitchen->fresh();
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $kitchen, 0, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  Kitchen  $kitchen  The kitchen
     * @param  int  $changesCount  Number of changed fields
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, Kitchen $kitchen, int $changesCount, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Kitchen update', [
            'action' => 'update_kitchen',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'kitchen_id' => $kitchen->id,
            'kitchen_name' => $kitchen->name,
            'changes_count' => $changesCount,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Kitchen  $kitchen  The kitchen to update
     * @param  KitchenData  $data  The kitchen data
     */
    public function asJob(Kitchen $kitchen, KitchenData $data): void
    {
        $this->handle($kitchen, $data);
    }
}
