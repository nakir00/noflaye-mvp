<?php

namespace App\Console\Commands;

use App\Models\PermissionAuditLog;
use Illuminate\Console\Command;

/**
 * CleanupAuditLogCommand
 *
 * Clean up old audit log entries
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class CleanupAuditLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:cleanup-audit
                            {--days=90 : Number of days to keep audit logs}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old audit log entries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("🔍 Searching for audit logs older than {$days} days (before {$cutoffDate->toDateString()})...");

        $oldLogs = PermissionAuditLog::where('created_at', '<', $cutoffDate);
        $count = $oldLogs->count();

        if ($count === 0) {
            $this->info('✅ No old audit logs found');

            return self::SUCCESS;
        }

        $this->warn("Found {$count} old audit log entries");

        if ($this->option('dry-run')) {
            $this->warn('🔸 DRY RUN MODE - No changes will be made');

            // Show sample of what would be deleted
            $sample = $oldLogs->limit(10)->get();

            $this->table(
                ['ID', 'User', 'Action', 'Permission', 'Created At'],
                $sample->map(fn ($log) => [
                    $log->id,
                    $log->user_name,
                    $log->action,
                    $log->permission_slug,
                    $log->created_at->toDateTimeString(),
                ])->toArray()
            );

            $this->info("Total that would be deleted: {$count}");

            return self::SUCCESS;
        }

        if (! $this->confirm("Delete {$count} audit log entries older than {$days} days?")) {
            $this->info('Cancelled');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        PermissionAuditLog::where('created_at', '<', $cutoffDate)
            ->chunkById(1000, function ($logs) use ($bar, &$deleted) {
                foreach ($logs as $log) {
                    $log->delete();
                    $deleted++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully deleted {$deleted} old audit log entries");

        return self::SUCCESS;
    }
}
