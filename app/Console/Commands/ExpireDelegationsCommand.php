<?php

namespace App\Console\Commands;

use App\Services\Permissions\PermissionDelegator;
use Illuminate\Console\Command;

/**
 * ExpireDelegationsCommand
 *
 * Clean up expired permission delegations
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class ExpireDelegationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:expire-delegations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired permission delegations';

    /**
     * Execute the console command.
     */
    public function handle(PermissionDelegator $delegator): int
    {
        $this->info('🔍 Searching for expired delegations...');

        $expiredCount = $delegator->expireExpiredDelegations();

        if ($expiredCount === 0) {
            $this->info('✅ No expired delegations found');
            return self::SUCCESS;
        }

        $this->info("✅ Found and logged {$expiredCount} expired delegations");
        $this->warn('💡 Note: Expired delegations are automatically inactive and do not need cleanup');

        return self::SUCCESS;
    }
}
