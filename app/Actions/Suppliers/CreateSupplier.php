<?php

namespace App\Actions\Suppliers;

use App\Data\Suppliers\SupplierData;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Create Supplier
 *
 * Creates a new supplier with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via SupplierData
 * - Activity logging with audit trail
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $data = SupplierData::from([
 *     'name' => 'ABC Supplies Co.',
 *     'slug' => 'abc-supplies',
 *     'email' => 'contact@abc-supplies.com',
 *     'phone' => '+221771234567',
 *     'address' => '456 Business Ave, Dakar',
 *     'is_active' => true,
 * ]);
 *
 * $supplier = CreateSupplier::run($data);
 * ```
 *
 * @see SupplierData The validated input DTO
 * @see Supplier The supplier model
 */
class CreateSupplier
{
    use AsAction;

    /**
     * Create a new supplier
     *
     * @param  SupplierData  $data  The validated supplier data
     * @return Supplier The created supplier instance
     *
     * @throws \Exception If creation fails
     */
    public function handle(SupplierData $data): Supplier
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($data, $startTime) {
                // Create supplier
                $supplier = Supplier::create([
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
                    ->performedOn($supplier)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'name' => $supplier->name,
                        'slug' => $supplier->slug,
                        'email' => $supplier->email,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('supplier_created');

                // Invalidate caches
                Cache::tags(['suppliers'])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $supplier);

                return $supplier;
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
     * @param  Supplier|null  $supplier  The created supplier
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, ?Supplier $supplier, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Supplier creation', [
            'action' => 'create_supplier',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'supplier_id' => $supplier?->id,
            'supplier_name' => $supplier?->name,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  SupplierData  $data  The supplier data
     */
    public function asJob(SupplierData $data): void
    {
        $this->handle($data);
    }
}
