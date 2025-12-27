<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Console\Command;

/**
 * WarmPermissionCacheCommand
 *
 * Pre-warm permission cache for all users
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class WarmPermissionCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:warm-cache
                            {--chunk=100 : Number of users to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-warm permission cache for all users';

    /**
     * Execute the console command.
     */
    public function handle(PermissionChecker $checker): int
    {
        $this->info('🔥 Warming permission cache...');

        $totalUsers = User::count();
        $this->info("Total users to process: {$totalUsers}");

        $chunkSize = (int) $this->option('chunk');
        $bar = $this->output->createProgressBar($totalUsers);

        $warmedCount = 0;

        User::with(['permissions', 'templates.permissions', 'templates.wildcards'])
            ->chunk($chunkSize, function ($users) use ($checker, $bar, &$warmedCount) {
                foreach ($users as $user) {
                    // Warm cache by getting all permissions
                    $checker->getAllUserPermissions($user);

                    $warmedCount++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully warmed cache for {$warmedCount} users");

        return self::SUCCESS;
    }
}
