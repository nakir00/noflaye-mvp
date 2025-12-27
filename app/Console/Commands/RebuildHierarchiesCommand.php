<?php

namespace App\Console\Commands;

use App\Models\UserGroup;
use App\Models\PermissionTemplate;
use App\Models\PermissionGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * RebuildHierarchiesCommand
 *
 * Rebuild all hierarchy closure tables
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class RebuildHierarchiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:rebuild-hierarchies
                            {--type= : Type of hierarchy to rebuild (user_groups, templates, permission_groups, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild all hierarchy closure tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type') ?? 'all';

        $this->info('🔨 Rebuilding hierarchies...');

        if ($type === 'all' || $type === 'user_groups') {
            $this->rebuildUserGroupHierarchy();
        }

        if ($type === 'all' || $type === 'templates') {
            $this->rebuildTemplateHierarchy();
        }

        if ($type === 'all' || $type === 'permission_groups') {
            $this->rebuildPermissionGroupHierarchy();
        }

        $this->info('✅ Hierarchies rebuilt successfully');

        return self::SUCCESS;
    }

    /**
     * Rebuild user group hierarchy
     */
    private function rebuildUserGroupHierarchy(): void
    {
        $this->info('📁 Rebuilding user group hierarchy...');

        DB::table('user_group_hierarchy')->truncate();

        $groups = UserGroup::all();
        $bar = $this->output->createProgressBar($groups->count());

        foreach ($groups as $group) {
            $ancestors = $this->findAncestors('user_groups', $group->id);

            foreach ($ancestors as $depth => $ancestorId) {
                DB::table('user_group_hierarchy')->insert([
                    'ancestor_id' => $ancestorId,
                    'descendant_id' => $group->id,
                    'depth' => $depth,
                ]);
            }

            $group->update(['level' => count($ancestors)]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Rebuild template hierarchy
     */
    private function rebuildTemplateHierarchy(): void
    {
        $this->info('📋 Rebuilding template hierarchy...');

        DB::table('permission_template_hierarchy')->truncate();

        $templates = PermissionTemplate::all();
        $bar = $this->output->createProgressBar($templates->count());

        foreach ($templates as $template) {
            $ancestors = $this->findAncestors('permission_templates', $template->id);

            foreach ($ancestors as $depth => $ancestorId) {
                DB::table('permission_template_hierarchy')->insert([
                    'ancestor_id' => $ancestorId,
                    'descendant_id' => $template->id,
                    'depth' => $depth,
                ]);
            }

            $template->update(['level' => count($ancestors)]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Rebuild permission group hierarchy
     */
    private function rebuildPermissionGroupHierarchy(): void
    {
        $this->info('🔐 Rebuilding permission group hierarchy...');

        DB::table('permission_group_hierarchy')->truncate();

        $groups = PermissionGroup::all();
        $bar = $this->output->createProgressBar($groups->count());

        foreach ($groups as $group) {
            $ancestors = $this->findAncestors('permission_groups', $group->id);

            foreach ($ancestors as $depth => $ancestorId) {
                DB::table('permission_group_hierarchy')->insert([
                    'ancestor_id' => $ancestorId,
                    'descendant_id' => $group->id,
                    'depth' => $depth,
                ]);
            }

            $group->update(['level' => count($ancestors)]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Find all ancestors recursively
     */
    private function findAncestors(string $table, int $id, int $depth = 0): array
    {
        $ancestors = [];

        $parent = DB::table($table)->where('id', $id)->value('parent_id');

        if ($parent) {
            $ancestors[$depth] = $parent;
            $ancestors = array_merge($ancestors, $this->findAncestors($table, $parent, $depth + 1));
        }

        return $ancestors;
    }
}
