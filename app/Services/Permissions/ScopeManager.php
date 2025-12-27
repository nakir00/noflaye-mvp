<?php

namespace App\Services\Permissions;

use App\Models\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * ScopeManager Service
 *
 * Centralized scope management for the permission system
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class ScopeManager
{
    /**
     * Create or retrieve scope for a given entity
     *
     * @param Model $entity The entity to scope (Shop, Kitchen, etc.)
     * @param bool $activate Whether to activate if deactivated
     * @return Scope
     */
    public function createScopeForEntity(Model $entity, bool $activate = true): Scope
    {
        $scopeKey = $this->makeScopeKey($entity);

        // Try to find existing scope
        $scope = Scope::where('scopable_type', get_class($entity))
            ->where('scopable_id', $entity->id)
            ->first();

        if ($scope) {
            // Reactivate if needed
            if ($activate && !$scope->is_active) {
                $scope->activate();
            }

            return $scope;
        }

        // Create new scope
        return Scope::create([
            'scopable_type' => get_class($entity),
            'scopable_id' => $entity->id,
            'scope_key' => $scopeKey,
            'name' => $this->getScopeName($entity),
            'is_active' => $activate,
        ]);
    }

    /**
     * Find or create scope by key
     *
     * @param string $scopeKey Format: "type:id" (e.g., "shop:5")
     * @return Scope|null
     */
    public function findOrCreateScope(string $scopeKey): ?Scope
    {
        // Try cache first
        $cacheKey = "scope:{$scopeKey}";

        return Cache::remember($cacheKey, 3600, function () use ($scopeKey) {
            $scope = Scope::where('scope_key', $scopeKey)->first();

            if ($scope) {
                return $scope;
            }

            // Parse scope key
            [$type, $id] = $this->parseScopeKey($scopeKey);

            if (!$type || !$id) {
                return null;
            }

            // Find entity
            $modelClass = $this->getModelClass($type);

            if (!$modelClass || !class_exists($modelClass)) {
                return null;
            }

            $entity = $modelClass::find($id);

            if (!$entity) {
                return null;
            }

            return $this->createScopeForEntity($entity);
        });
    }

    /**
     * Get scope by ID with caching
     *
     * @param int $scopeId
     * @return Scope|null
     */
    public function getScopeById(int $scopeId): ?Scope
    {
        $cacheKey = "scope:id:{$scopeId}";

        return Cache::remember($cacheKey, 3600, function () use ($scopeId) {
            return Scope::find($scopeId);
        });
    }

    /**
     * Deactivate a scope
     *
     * @param Scope $scope
     * @return bool
     */
    public function deactivateScope(Scope $scope): bool
    {
        $result = $scope->deactivate();

        if ($result) {
            // Invalidate cache
            Cache::forget("scope:{$scope->scope_key}");
            Cache::forget("scope:id:{$scope->id}");
        }

        return $result;
    }

    /**
     * Get all scopes by type
     *
     * @param string $type Entity type (shop, kitchen, etc.)
     * @param bool $activeOnly Only active scopes
     * @return Collection<Scope>
     */
    public function getScopesByType(string $type, bool $activeOnly = true): Collection
    {
        $modelClass = $this->getModelClass($type);

        $query = Scope::where('scopable_type', $modelClass);

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Bulk create scopes for entities
     *
     * @param Collection $entities
     * @return Collection<Scope>
     */
    public function bulkCreateScopes(Collection $entities): Collection
    {
        $scopes = collect();

        foreach ($entities as $entity) {
            $scopes->push($this->createScopeForEntity($entity, false));
        }

        return $scopes;
    }

    /**
     * Make scope key from entity
     *
     * @param Model $entity
     * @return string
     */
    private function makeScopeKey(Model $entity): string
    {
        $type = strtolower(class_basename($entity));
        return "{$type}:{$entity->id}";
    }

    /**
     * Get scope name from entity
     *
     * @param Model $entity
     * @return string|null
     */
    private function getScopeName(Model $entity): ?string
    {
        return $entity->name ?? $entity->title ?? null;
    }

    /**
     * Parse scope key into type and id
     *
     * @param string $scopeKey
     * @return array [type, id]
     */
    private function parseScopeKey(string $scopeKey): array
    {
        $parts = explode(':', $scopeKey);

        if (count($parts) !== 2) {
            return [null, null];
        }

        return [$parts[0], (int) $parts[1]];
    }

    /**
     * Get model class from type
     *
     * @param string $type
     * @return string|null
     */
    private function getModelClass(string $type): ?string
    {
        return match($type) {
            'shop' => \App\Models\Shop::class,
            'kitchen' => \App\Models\Kitchen::class,
            'driver' => \App\Models\Driver::class,
            'supervisor' => \App\Models\Supervisor::class,
            'supplier' => \App\Models\Supplier::class,
            'user' => \App\Models\User::class,
            default => null,
        };
    }

    /**
     * Invalidate all scope caches
     *
     * @return void
     */
    public function invalidateAllCaches(): void
    {
        Cache::tags(['scopes'])->flush();
    }
}
