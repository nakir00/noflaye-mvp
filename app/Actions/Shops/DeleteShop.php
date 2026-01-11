<?php

namespace App\Actions\Shops;

use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Delete Shop
 *
 * Deletes a shop with audit logging and cache management.
 *
 * Features:
 * - Soft delete support (if enabled on model)
 * - Activity logging with shop details
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $shop = Shop::find(1);
 * $deleted = DeleteShop::run($shop);
 * ```
 *
 * @see Shop The shop model
 */
class DeleteShop
{
    use AsAction;

    /**
     * Delete a shop
     *
     * @param  Shop  $shop  The shop to delete
     * @param  bool  $force  Whether to force delete (skip soft delete)
     * @return bool True if deletion was successful
     *
     * @throws \Exception If deletion fails
     */
    public function handle(Shop $shop, bool $force = false): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($shop, $force, $startTime) {
                // Store shop info before deletion
                $shopId = $shop->id;
                $shopName = $shop->name;
                $shopData = [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'slug' => $shop->slug,
                    'phone' => $shop->phone,
                    'address' => $shop->address,
                ];

                // Log activity before deletion
                activity()
                    ->performedOn($shop)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'shop_data' => $shopData,
                        'force_delete' => $force,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('shop_deleted');

                // Delete shop
                $deleted = $force ? $shop->forceDelete() : $shop->delete();

                // Invalidate caches
                Cache::tags(['shops', "shop.{$shopId}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $shopId, $shopName, $force);

                return $deleted;
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $shop->id, $shop->name, $force, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  int  $shopId  The shop ID
     * @param  string  $shopName  The shop name
     * @param  bool  $force  Whether it was a force delete
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, int $shopId, string $shopName, bool $force, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Shop deletion', [
            'action' => 'delete_shop',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'shop_id' => $shopId,
            'shop_name' => $shopName,
            'force_delete' => $force,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Shop  $shop  The shop to delete
     * @param  bool  $force  Whether to force delete
     */
    public function asJob(Shop $shop, bool $force = false): void
    {
        $this->handle($shop, $force);
    }
}
