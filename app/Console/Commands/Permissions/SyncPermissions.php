<?php

namespace App\Console\Commands\Permissions;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Artisan Command: Sync Permissions
 *
 * This command synchronizes user permissions with their assigned templates,
 * ensuring all permissions are up-to-date based on template definitions.
 * Useful for maintaining consistency after template permission changes.
 *
 * Features:
 * - Syncs user permissions with template permissions
 * - Handles auto-sync enabled templates
 * - Removes orphaned permissions
 * - Provides detailed statistics
 * - Dry-run mode for safety
 * - Cache invalidation
 *
 * Usage:
 * ```bash
 * php artisan permissions:sync
 * php artisan permissions:sync --dry-run
 * php artisan permissions:sync --user=123
 * php artisan permissions:sync --template=shop-manager
 * php artisan permissions:sync --force
 * ```
 */
class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync
                            {--dry-run : Run without making database changes}
                            {--user= : Sync permissions for specific user ID}
                            {--template= : Sync only users with specific template slug}
                            {--force : Force sync even if auto-sync is disabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize user permissions with their assigned templates';

    /**
     * Execute the console command.
     *
     * Syncs permissions for all users or a specific user based on their
     * assigned templates and auto-sync settings.
     *
     * @return int Command exit code (0 for success)
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $userId = $this->option('user');
        $templateSlug = $this->option('template');
        $force = $this->option('force');

        $this->info('🔄 Synchronizing user permissions with templates...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No database changes will be made');
            $this->newLine();
        }

        try {
            return DB::transaction(function () use ($isDryRun, $userId, $templateSlug, $force) {
                // Get users to sync
                $users = $this->getUsersToSync($userId, $templateSlug);

                if ($users->isEmpty()) {
                    $this->warn('No users found to sync.');

                    return self::SUCCESS;
                }

                $this->info("Found {$users->count()} user(s) to sync");
                $this->newLine();

                $stats = [
                    'users_processed' => 0,
                    'permissions_added' => 0,
                    'permissions_removed' => 0,
                    'permissions_skipped' => 0,
                ];

                $progressBar = $this->output->createProgressBar($users->count());
                $progressBar->start();

                foreach ($users as $user) {
                    $result = $this->syncUserPermissions($user, $isDryRun, $force);

                    $stats['users_processed']++;
                    $stats['permissions_added'] += $result['added'];
                    $stats['permissions_removed'] += $result['removed'];
                    $stats['permissions_skipped'] += $result['skipped'];

                    $progressBar->advance();
                }

                $progressBar->finish();
                $this->newLine(2);

                // Display statistics
                $this->displayStatistics($stats);

                // Invalidate cache
                if (! $isDryRun) {
                    Cache::tags(['permissions', 'users'])->flush();
                    $this->info('Cache invalidated');
                }

                if ($isDryRun) {
                    $this->newLine();
                    $this->warn('⚠️  This was a dry run. Run without --dry-run to apply changes.');
                }

                return self::SUCCESS;
            });
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Get users to sync based on filters
     *
     * @param  int|null  $userId  Specific user ID
     * @param  string|null  $templateSlug  Template slug filter
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUsersToSync(?int $userId, ?string $templateSlug)
    {
        $query = User::with(['templates.permissions', 'permissions']);

        if ($userId) {
            $query->where('id', $userId);
        }

        if ($templateSlug) {
            $query->whereHas('templates', function ($q) use ($templateSlug) {
                $q->where('slug', $templateSlug);
            });
        }

        return $query->get();
    }

    /**
     * Sync permissions for a single user
     *
     * @param  User  $user  The user to sync
     * @param  bool  $isDryRun  Whether this is a dry run
     * @param  bool  $force  Force sync regardless of auto-sync setting
     * @return array Statistics for this user
     */
    protected function syncUserPermissions(User $user, bool $isDryRun, bool $force): array
    {
        $stats = ['added' => 0, 'removed' => 0, 'skipped' => 0];

        // Get all template permissions that should be synced
        $templatePermissions = collect();
        foreach ($user->templates as $template) {
            $pivot = $user->templates()->where('permission_template_id', $template->id)->first()?->pivot;

            // Skip if auto-sync is disabled and not forced
            if (! $force && ! $pivot?->auto_sync) {
                $stats['skipped'] += $template->permissions->count();

                continue;
            }

            $templatePermissions = $templatePermissions->merge($template->permissions);
        }

        // Get current user permissions (direct assignments)
        $currentPermissions = $user->permissions;

        // Determine which permissions to add
        $toAdd = $templatePermissions->whereNotIn('id', $currentPermissions->pluck('id'));

        // Determine which template-based permissions to remove
        // (Don't remove direct assignments)
        $toRemove = $currentPermissions->filter(function ($permission) use ($templatePermissions, $user) {
            $pivot = $user->permissions()->where('permission_id', $permission->id)->first()?->pivot;

            return $pivot && $pivot->source === 'template' && ! $templatePermissions->contains('id', $permission->id);
        });

        // Apply changes
        if (! $isDryRun) {
            foreach ($toAdd as $permission) {
                $user->permissions()->attach($permission->id, [
                    'source' => 'template',
                    'granted_at' => now(),
                ]);
                $stats['added']++;
            }

            foreach ($toRemove as $permission) {
                $user->permissions()->detach($permission->id);
                $stats['removed']++;
            }
        } else {
            $stats['added'] = $toAdd->count();
            $stats['removed'] = $toRemove->count();
        }

        return $stats;
    }

    /**
     * Display synchronization statistics
     *
     * @param  array  $stats  The statistics array
     */
    protected function displayStatistics(array $stats): void
    {
        $this->components->info('✅ Synchronization completed');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Users Processed', $stats['users_processed']],
                ['Permissions Added', $stats['permissions_added']],
                ['Permissions Removed', $stats['permissions_removed']],
                ['Permissions Skipped', $stats['permissions_skipped']],
                ['Total Changes', $stats['permissions_added'] + $stats['permissions_removed']],
            ]
        );
    }
}
