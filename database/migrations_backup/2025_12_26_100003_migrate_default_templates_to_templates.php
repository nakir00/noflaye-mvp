<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Migration: Migrate default_permission_templates to permission_templates
 *
 * Purpose: Copy default templates to permission_templates with new IDs
 *          to avoid ID collision with migrated roles
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        echo "🔄 Starting default templates migration...\n";

        DB::transaction(function () {
            // STEP 1: Get max ID from permission_templates (migrated roles)
            echo "  → Finding max template ID...\n";
            $maxId = DB::table('permission_templates')->max('id') ?? 0;

            // STEP 2: Retrieve default templates
            echo "  → Fetching default templates...\n";
            $defaultTemplates = DB::table('default_permission_templates')->get();
            $templateCount = $defaultTemplates->count();

            if ($templateCount === 0) {
                echo "⚠️  No default templates found to migrate\n";
                return;
            }

            // STEP 3: Prepare inserts with new IDs
            echo "  → Preparing {$templateCount} templates with new IDs...\n";
            $inserts = [];
            $idMapping = []; // Old ID → New ID

            foreach ($defaultTemplates as $template) {
                $newId = ++$maxId;
                $idMapping[$template->id] = $newId;

                $inserts[] = [
                    'id' => $newId,
                    'name' => $template->name,
                    'slug' => $template->slug ?? Str::slug($template->name),
                    'description' => $template->description ?? null,
                    'parent_id' => null,
                    'scope_id' => null,
                    'color' => $template->color ?? 'primary',
                    'icon' => $template->icon ?? 'heroicon-o-clipboard-list',
                    'level' => 0,
                    'sort_order' => $template->sort_order ?? 0,
                    'is_active' => $template->is_active ?? true,
                    'is_system' => $template->is_system ?? true,
                    'auto_sync_users' => true,
                    'created_at' => $template->created_at ?? now(),
                    'updated_at' => $template->updated_at ?? now(),
                ];
            }

            // STEP 4: Bulk insert
            echo "  → Inserting templates...\n";
            DB::table('permission_templates')->insert($inserts);

            // STEP 5: Store mapping for next migrations
            echo "  → Caching ID mapping for next migrations...\n";
            cache()->put('default_template_id_mapping', $idMapping, 3600);

            echo "✅ Migrated {$templateCount} default templates\n";
            echo "ℹ️  ID mapping cached for next migrations\n";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Removing migrated default templates...\n";

        DB::transaction(function () {
            $idMapping = cache()->get('default_template_id_mapping', []);
            $newIds = array_values($idMapping);

            if (!empty($newIds)) {
                DB::table('permission_templates')
                    ->whereIn('id', $newIds)
                    ->delete();
            }

            cache()->forget('default_template_id_mapping');
            echo "✅ Removed migrated default templates\n";
        });
    }
};
