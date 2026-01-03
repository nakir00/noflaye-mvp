<?php

namespace App\Console\Commands\Permissions;

use App\Data\Permissions\PermissionData;
use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Artisan Command: Generate Permissions from Enum
 *
 * This command generates Permission records in the database based on the
 * Permission enum cases. It ensures all enum-defined permissions exist in
 * the database with proper metadata.
 *
 * Features:
 * - Syncs all Permission enum cases to database
 * - Creates default permission group if needed
 * - Updates existing permissions without duplicates
 * - Provides detailed output with statistics
 *
 * Usage:
 * ```bash
 * php artisan permissions:generate-from-enum
 * php artisan permissions:generate-from-enum --dry-run
 * php artisan permissions:generate-from-enum --group="Core Permissions"
 * ```
 */
class GeneratePermissionsFromEnum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:generate-from-enum
                            {--dry-run : Run without making database changes}
                            {--group= : Permission group name (default: "System Permissions")}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate permission records from Permission enum cases';

    /**
     * Execute the console command.
     *
     * Iterates through all Permission enum cases and creates/updates
     * corresponding database records.
     *
     * @return int Command exit code (0 for success)
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $groupName = $this->option('group') ?? 'System Permissions';

        $this->info('🔐 Generating permissions from Permission enum...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No database changes will be made');
            $this->newLine();
        }

        try {
            $stats = DB::transaction(function () use ($isDryRun, $groupName) {
                // Get or create permission group
                $group = $this->getOrCreatePermissionGroup($groupName, $isDryRun);

                if (! $group && ! $isDryRun) {
                    $this->error('Failed to get or create permission group');

                    return null;
                }

                $enumCases = PermissionEnum::cases();
                $created = 0;
                $updated = 0;
                $skipped = 0;

                $progressBar = $this->output->createProgressBar(count($enumCases));
                $progressBar->start();

                foreach ($enumCases as $enumCase) {
                    $result = $this->processPermission($enumCase, $group?->id ?? 1, $isDryRun);

                    match ($result) {
                        'created' => $created++,
                        'updated' => $updated++,
                        'skipped' => $skipped++,
                        default => null,
                    };

                    $progressBar->advance();
                }

                $progressBar->finish();
                $this->newLine(2);

                return compact('created', 'updated', 'skipped', 'group');
            });

            if (! $stats) {
                return self::FAILURE;
            }

            // Display statistics
            $this->components->info('✅ Permission generation completed');
            $this->newLine();

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Created', $stats['created']],
                    ['Updated', $stats['updated']],
                    ['Skipped', $stats['skipped']],
                    ['Total Processed', array_sum([$stats['created'], $stats['updated'], $stats['skipped']])],
                ]
            );

            if ($isDryRun) {
                $this->newLine();
                $this->warn('⚠️  This was a dry run. Run without --dry-run to apply changes.');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Get existing or create new permission group
     *
     * @param  string  $groupName  The group name
     * @param  bool  $isDryRun  Whether this is a dry run
     * @return PermissionGroup|null The permission group or null in dry run
     */
    protected function getOrCreatePermissionGroup(string $groupName, bool $isDryRun): ?PermissionGroup
    {
        if ($isDryRun) {
            $this->line("Would create/use permission group: {$groupName}");

            return null;
        }

        $group = PermissionGroup::firstOrCreate(
            ['slug' => str($groupName)->slug()],
            [
                'name' => $groupName,
                'description' => 'Auto-generated from Permission enum',
                'is_system' => true,
            ]
        );

        $this->line("📁 Using permission group: {$group->name} (ID: {$group->id})");
        $this->newLine();

        return $group;
    }

    /**
     * Process a single permission enum case
     *
     * Creates or updates the permission record in the database.
     *
     * @param  PermissionEnum  $enumCase  The permission enum case
     * @param  int  $groupId  The permission group ID
     * @param  bool  $isDryRun  Whether this is a dry run
     * @return string Result: 'created', 'updated', or 'skipped'
     */
    protected function processPermission(PermissionEnum $enumCase, int $groupId, bool $isDryRun): string
    {
        $slug = $enumCase->value;
        $existing = Permission::where('slug', $slug)->first();

        if ($isDryRun) {
            if ($existing) {
                return 'skipped';
            }

            return 'created';
        }

        if ($existing) {
            // Permission already exists, skip or update if needed
            return 'skipped';
        }

        // Create new permission
        $data = PermissionData::fromEnum($enumCase, $groupId);

        Permission::create([
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'permission_group_id' => $data->permission_group_id,
            'is_active' => $data->is_active,
            'is_system' => $data->is_system,
        ]);

        return 'created';
    }
}
