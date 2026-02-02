<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Trait HasHierarchy
 *
 * Provides hierarchical relationships for models using closure tables.
 * Implements parent/children relationships with full ancestry/descendant support.
 *
 * Required:
 * - Model must have a `parent_id` column
 * - Model must have a hierarchy pivot table (e.g., permission_template_hierarchy)
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
trait HasHierarchy
{
    /**
     * Get the hierarchy table name.
     * Override in model if different from default.
     */
    public function getHierarchyTable(): string
    {
        return property_exists($this, 'hierarchyTable')
            ? $this->hierarchyTable
            : $this->getTable().'_hierarchy';
    }

    /**
     * Get the ancestor foreign key name.
     */
    public function getAncestorKeyName(): string
    {
        return 'ancestor_id';
    }

    /**
     * Get the descendant foreign key name.
     */
    public function getDescendantKeyName(): string
    {
        return 'descendant_id';
    }

    // ========================================
    // DIRECT RELATIONSHIPS (via parent_id)
    // ========================================

    /**
     * Get the direct parent.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * Get direct children ordered by sort_order.
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')
            ->orderBy('sort_order');
    }

    // ========================================
    // HIERARCHY RELATIONSHIPS (via closure table)
    // ========================================

    /**
     * Get all ancestors (parents up to root) ordered by depth (closest first).
     */
    public function ancestors(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            $this->getHierarchyTable(),
            $this->getDescendantKeyName(),
            $this->getAncestorKeyName()
        )
            ->withPivot(['depth', 'sort_order'])
            ->orderBy('depth', 'asc');
    }

    /**
     * Alias for ancestors() - more intuitive name.
     */
    public function parents(): BelongsToMany
    {
        return $this->ancestors();
    }

    /**
     * Get all descendants (children down to leaves) ordered by depth then sort_order.
     */
    public function descendants(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            $this->getHierarchyTable(),
            $this->getAncestorKeyName(),
            $this->getDescendantKeyName()
        )
            ->withPivot(['depth', 'sort_order'])
            ->orderBy('depth', 'asc')
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Alias for descendants() - more intuitive name.
     */
    public function allChildren(): BelongsToMany
    {
        return $this->descendants();
    }

    // ========================================
    // HIERARCHY HELPER METHODS
    // ========================================

    /**
     * Check if this model is a root (no parent).
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if this model is a leaf (no children).
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Check if this model is an ancestor of another.
     */
    public function isAncestorOf(self $descendant): bool
    {
        return $descendant->ancestors()
            ->where($this->getAncestorKeyName(), $this->id)
            ->exists();
    }

    /**
     * Check if this model is a descendant of another.
     */
    public function isDescendantOf(self $ancestor): bool
    {
        return $this->ancestors()
            ->where($this->getAncestorKeyName(), $ancestor->id)
            ->exists();
    }

    /**
     * Get the depth level of this model in the hierarchy.
     * 0 = root, 1 = first level, etc.
     */
    public function getDepth(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * Get ancestors as a flat collection ordered from root to direct parent.
     */
    public function getAncestorsFromRoot(): Collection
    {
        return $this->ancestors()
            ->orderBy('depth', 'desc')
            ->get();
    }

    /**
     * Get the path from root to this model.
     */
    public function getPath(): Collection
    {
        return $this->getAncestorsFromRoot()->push($this);
    }

    /**
     * Get siblings (models with the same parent).
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id', 'parent_id')
            ->where('id', '!=', $this->id)
            ->orderBy('sort_order');
    }

    // ========================================
    // HIERARCHY MANAGEMENT METHODS
    // ========================================

    /**
     * Rebuild the hierarchy closure table for this model.
     * Call this after changing parent_id.
     */
    public function rebuildHierarchy(): void
    {
        $table = $this->getHierarchyTable();
        $ancestorKey = $this->getAncestorKeyName();
        $descendantKey = $this->getDescendantKeyName();

        // Remove all ancestor relationships for this node
        DB::table($table)
            ->where($descendantKey, $this->id)
            ->delete();

        // Add self-reference (depth 0)
        DB::table($table)->insert([
            $ancestorKey => $this->id,
            $descendantKey => $this->id,
            'depth' => 0,
            'sort_order' => $this->sort_order ?? 0,
        ]);

        // Add parent hierarchy if exists
        if ($this->parent_id) {
            $parentAncestors = DB::table($table)
                ->where($descendantKey, $this->parent_id)
                ->get();

            foreach ($parentAncestors as $ancestor) {
                DB::table($table)->insert([
                    $ancestorKey => $ancestor->{$ancestorKey},
                    $descendantKey => $this->id,
                    'depth' => $ancestor->depth + 1,
                    'sort_order' => $this->sort_order ?? 0,
                ]);
            }
        }

        // Rebuild all descendants
        $this->children->each(function ($child) {
            $child->rebuildHierarchy();
        });
    }

    /**
     * Move this model under a new parent.
     */
    public function moveTo(?self $newParent): bool
    {
        // Prevent moving to self or descendant
        if ($newParent && ($newParent->id === $this->id || $newParent->isDescendantOf($this))) {
            return false;
        }

        $this->parent_id = $newParent?->id;
        $this->save();

        $this->rebuildHierarchy();

        return true;
    }

    /**
     * Boot the trait - set up model events for hierarchy maintenance.
     */
    public static function bootHasHierarchy(): void
    {
        static::created(function ($model) {
            $model->rebuildHierarchy();
        });

        static::updated(function ($model) {
            if ($model->isDirty('parent_id')) {
                $model->rebuildHierarchy();
            }
        });

        static::deleting(function ($model) {
            // Delete all hierarchy entries for this node and its descendants
            $table = $model->getHierarchyTable();
            $descendantKey = $model->getDescendantKeyName();

            // Get all descendants first
            $descendantIds = $model->descendants()->pluck('id')->push($model->id);

            DB::table($table)
                ->whereIn($descendantKey, $descendantIds)
                ->delete();
        });
    }
}
