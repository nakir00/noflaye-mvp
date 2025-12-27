<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ExpirePermissionsCommand
 *
 * Expire user permissions that have passed their expiration date
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class ExpirePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:expire
                            {--dry-run : Show what would be expired without actually expiring}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire user permissions that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Searching for expired permissions...');

        // Find expired permissions
        $expiredPermissions = DB::table('user_permissions')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredPermissions->isEmpty()) {
            $this->info('✅ No expired permissions found');
            return self::SUCCESS;
        }

        $this->info("Found {$expiredPermissions->count()} expired permissions");

        if ($this->option('dry-run')) {
            $this->warn('🔸 DRY RUN MODE - No changes will be made');

            $this->table(
                ['User ID', 'Permission ID', 'Expired At'],
                $expiredPermissions->map(fn($p) => [
                    $p->user_id,
                    $p->permission_id,
                    $p->expires_at,
                ])->toArray()
            );

            return self::SUCCESS;
        }

        // Create progress bar
        $bar = $this->output->createProgressBar($expiredPermissions->count());
        $bar->start();

        $deletedCount = 0;

        foreach ($expiredPermissions as $permission) {
            DB::table('user_permissions')
                ->where('user_id', $permission->user_id)
                ->where('permission_id', $permission->permission_id)
                ->delete();

            $deletedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully expired {$deletedCount} permissions");

        return self::SUCCESS;
    }
}
