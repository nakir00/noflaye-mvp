<?php

namespace App\Actions\Shops;

use App\Data\Shops\ShopData;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Update Shop
 *
 * Updates an existing shop with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via ShopData
 * - Change tracking (only modified fields)
 * - Activity logging with before/after values
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $shop = Shop::find(1);
 * $data = ShopData::from([
 *     'name' => 'Downtown Shop Updated',
 *     'slug' => 'downtown-shop-updated',
 *     'is_active' => false,
 * ]);
 *
 * $updated = UpdateShop::run($shop, $data);
 * ```
 *
 * @see ShopData The validated input DTO
 * @see Shop The shop model
 */
class UpdateShop
{
    use AsAction;

    /**
     * Update a shop
     *
     * @param  Shop  $shop  The shop to update
     * @param  ShopData  $data  The validated shop data
     * @return Shop The updated shop instance
     *
     * @throws \Exception If update fails
     */
    public function handle(Shop $shop, ShopData $data): Shop
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($shop, $data, $startTime) {
                // Track changes
                $original = $shop->getOriginal();

                // Update shop
                $shop->update([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'phone' => $data->phone,
                    'address' => $data->address,
                    'is_active' => $data->is_active,
                ]);

                // Get actual changes
                $changes = $shop->getChanges();

                // Log activity if there are changes
                if (! empty($changes)) {
                    activity()
                        ->performedOn($shop)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'changes' => $changes,
                            'old' => array_intersect_key($original, $changes),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ])
                        ->log('shop_updated');
                }

                // Invalidate caches
                Cache::tags(['shops', "shop.{$shop->id}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $shop, count($changes));

                return $shop->fresh();
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $shop, 0, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  Shop  $shop  The shop
     * @param  int  $changesCount  Number of changed fields
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, Shop $shop, int $changesCount, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Shop update', [
            'action' => 'update_shop',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'shop_id' => $shop->id,
            'shop_name' => $shop->name,
            'changes_count' => $changesCount,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Shop  $shop  The shop to update
     * @param  ShopData  $data  The shop data
     */
    public function asJob(Shop $shop, ShopData $data): void
    {
        $this->handle($shop, $data);
    }
}
