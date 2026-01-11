<?php

namespace App\Actions\Kitchens;

use App\Models\Kitchen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Delete Kitchen
 *
 * Deletes a kitchen with audit logging and cache management.
 *
 * Features:
 * - Soft delete support (if enabled on model)
 * - Activity logging with kitchen details
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $kitchen = Kitchen::find(1);
 * $deleted = DeleteKitchen::run($kitchen);
 * ```
 *
 * @see Kitchen The kitchen model
 */
class DeleteKitchen
{
    use AsAction;

    /**
     * Delete a kitchen
     *
     * @param  Kitchen  $kitchen  The kitchen to delete
     * @param  bool  $force  Whether to force delete (skip soft delete)
     * @return bool True if deletion was successful
     *
     * @throws \Exception If deletion fails
     */
    public function handle(Kitchen $kitchen, bool $force = false): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($kitchen, $force, $startTime) {
                // Store kitchen info before deletion
                $kitchenId = $kitchen->id;
                $kitchenName = $kitchen->name;
                $kitchenData = [
                    'id' => $kitchen->id,
                    'name' => $kitchen->name,
                    'slug' => $kitchen->slug,
                    'address' => $kitchen->address,
                ];

                // Log activity before deletion
                activity()
                    ->performedOn($kitchen)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'kitchen_data' => $kitchenData,
                        'force_delete' => $force,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('kitchen_deleted');

                // Delete kitchen
                $deleted = $force ? $kitchen->forceDelete() : $kitchen->delete();

                // Invalidate caches
                Cache::tags(['kitchens', "kitchen.{$kitchenId}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $kitchenId, $kitchenName, $force);

                return $deleted;
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $kitchen->id, $kitchen->name, $force, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  int  $kitchenId  The kitchen ID
     * @param  string  $kitchenName  The kitchen name
     * @param  bool  $force  Whether it was a force delete
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, int $kitchenId, string $kitchenName, bool $force, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Kitchen deletion', [
            'action' => 'delete_kitchen',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'kitchen_id' => $kitchenId,
            'kitchen_name' => $kitchenName,
            'force_delete' => $force,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Kitchen  $kitchen  The kitchen to delete
     * @param  bool  $force  Whether to force delete
     */
    public function asJob(Kitchen $kitchen, bool $force = false): void
    {
        $this->handle($kitchen, $force);
    }
}
