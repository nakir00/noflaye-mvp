<?php

namespace App\Actions\Suppliers;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Action: Delete Supplier
 *
 * Deletes a supplier with audit logging and cache management.
 *
 * Features:
 * - Soft delete support (if enabled on model)
 * - Activity logging with supplier details
 * - Cache invalidation
 * - Performance metrics tracking
 * - Transaction safety
 *
 * Usage:
 * ```php
 * $supplier = Supplier::find(1);
 * $deleted = DeleteSupplier::run($supplier);
 * ```
 *
 * @see Supplier The supplier model
 */
class DeleteSupplier
{
    use AsAction;

    /**
     * Delete a supplier
     *
     * @param  Supplier  $supplier  The supplier to delete
     * @param  bool  $force  Whether to force delete (skip soft delete)
     * @return bool True if deletion was successful
     *
     * @throws \Exception If deletion fails
     */
    public function handle(Supplier $supplier, bool $force = false): bool
    {
        $startTime = microtime(true);

        try {
            return DB::transaction(function () use ($supplier, $force, $startTime) {
                // Store supplier info before deletion
                $supplierId = $supplier->id;
                $supplierName = $supplier->name;
                $supplierData = [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'slug' => $supplier->slug,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                ];

                // Log activity before deletion
                activity()
                    ->performedOn($supplier)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'supplier_data' => $supplierData,
                        'force_delete' => $force,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('supplier_deleted');

                // Delete supplier
                $deleted = $force ? $supplier->forceDelete() : $supplier->delete();

                // Invalidate caches
                Cache::tags(['suppliers', "supplier.{$supplierId}"])->flush();

                // Log metrics
                $this->logMetrics($startTime, 'success', $supplierId, $supplierName, $force);

                return $deleted;
            });
        } catch (\Exception $e) {
            $this->logMetrics($startTime, 'failed', $supplier->id, $supplier->name, $force, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Log performance metrics
     *
     * @param  float  $startTime  The start timestamp
     * @param  string  $outcome  The operation outcome
     * @param  int  $supplierId  The supplier ID
     * @param  string  $supplierName  The supplier name
     * @param  bool  $force  Whether it was a force delete
     * @param  string|null  $error  The error message if any
     */
    protected function logMetrics(float $startTime, string $outcome, int $supplierId, string $supplierName, bool $force, ?string $error = null): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('Supplier deletion', [
            'action' => 'delete_supplier',
            'outcome' => $outcome,
            'duration_ms' => round($duration, 2),
            'supplier_id' => $supplierId,
            'supplier_name' => $supplierName,
            'force_delete' => $force,
            'error' => $error,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Run as a queued job
     *
     * @param  Supplier  $supplier  The supplier to delete
     * @param  bool  $force  Whether to force delete
     */
    public function asJob(Supplier $supplier, bool $force = false): void
    {
        $this->handle($supplier, $force);
    }
}
