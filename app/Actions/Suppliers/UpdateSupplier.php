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
 * Action: Update Supplier
 *
 * Updates an existing supplier with validation, audit logging, and cache management.
 *
 * Features:
 * - Validated input via SupplierData
 * - Change tracking (only modified fields)
 * - Activity logging with before/after values
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $supplier = Supplier::find(1);
 * $data = SupplierData::from([
 *     'name' => 'ABC Supplies Updated',
 *     'slug' => 'abc-supplies-updated',
 *     'is_active' => false,
 * ]);
 *
 * $updated = UpdateSupplier::run($supplier, $data);
 * ```
 *
 * @see SupplierData The validated input DTO
 * @see Supplier The supplier model
 */
class UpdateSupplier
{
    use AsAction;

    /**
     * Update a supplier
     *
     * @param  Supplier  $supplier  The supplier to update
     * @param  SupplierData  $data  The validated supplier data
     * @return Supplier The updated supplier instance
     *
     * @throws \Exception If update fails
     */
    public function handle(Supplier $supplier, SupplierData $data): Supplier
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($supplier, $data, $startTime) {
                // Track changes
                $original = $supplier->getOriginal();

                // Update supplier
                $supplier->update([
                    'name' => $data->name,
                    'slug' => $data->slug,
                    'description' => $data->description,
                    'phone' => $data->phone,
                    'email' => $data->email,
                    'address' => $data->address,
                    'is_active' => $data->is_active,
                ]);

                // Get actual changes
                $changes = $supplier->getChanges();

                // Log activity if there are changes
                if (! empty($changes)) {
                    activity()
                        ->performedOn($supplier)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'changes' => $changes,
                            'old' => array_intersect_key($original, $changes),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ])
                        ->log('supplier_updated');
                }

                // Invalidate caches
                Cache::tags(['suppliers', "supplier.{$supplier->id}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $supplier, count($changes));

                return $supplier->fresh();
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $supplier, 0, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  Supplier  $supplier  The supplier
     * @param  int  $changesCount  Number of changed fields
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, Supplier $supplier, int $changesCount, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Supplier update', [
            'action' => 'update_supplier',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'changes_count' => $changesCount,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Supplier  $supplier  The supplier to update
     * @param  SupplierData  $data  The supplier data
     */
    public function asJob(Supplier $supplier, SupplierData $data): void
    {
        $this->handle($supplier, $data);
    }
}
