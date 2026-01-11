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
 * Action: Create Shop
 *
 * Creates a new shop with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via ShopData
 * - Activity logging with audit trail
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $data = ShopData::from([
 *     'name' => 'Downtown Shop',
 *     'slug' => 'downtown-shop',
 *     'email' => 'downtown@example.com',
 *     'phone' => '+221771234567',
 *     'address' => '123 Main St, Dakar',
 *     'is_active' => true,
 * ]);
 *
 * $shop = CreateShop::run($data);
 * ```
 *
 * @see ShopData The validated input DTO
 * @see Shop The shop model
 */
class CreateShop
{
    use AsAction;

    /**
     * Create a new shop
     *
     * @param  ShopData  $data  The validated shop data
     * @return Shop The created shop instance
     *
     * @throws \Exception If creation fails
     */
    public function handle(ShopData $data): Shop
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $startTime) {
                // Create shop
                $shop = Shop::create([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'phone' => $data->phone,
                    'address' => $data->address,
                    'is_active' => $data->is_active,
                ]);

                // Log activity
                activity()
                    ->performedOn($shop)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'name' => $shop->name,
                        'slug' => $shop->slug,
                        'address' => $shop->address,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('shop_created');

                // Invalidate caches
                Cache::tags(['shops'])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $shop);

                return $shop;
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
     * @param  Shop|null  $shop  The created shop
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, ?Shop $shop, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Shop creation', [
            'action' => 'create_shop',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'shop_id' => $shop?->id,
            'shop_name' => $shop?->name,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  ShopData  $data  The shop data
     */
    public function asJob(ShopData $data): void
    {
        $this->handle($data);
    }
}
