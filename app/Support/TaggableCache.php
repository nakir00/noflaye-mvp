<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Taggable Cache Helper
 *
 * Provides fallback for cache drivers that don't support tagging
 */
class TaggableCache
{
    /**
     * Check if cache driver supports tagging
     */
    public static function supportsTags(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }

    /**
     * Flush cache by tags with fallback
     */
    public static function flushTags(array $tags): void
    {
        if (self::supportsTags()) {
            Cache::tags($tags)->flush();
        } else {
            // Fallback: flush all cache
            Cache::flush();
        }
    }

    /**
     * Get tagged cache instance or fallback to regular cache
     */
    public static function tags(array $tags): mixed
    {
        if (self::supportsTags()) {
            return Cache::tags($tags);
        }

        return Cache::store();
    }
}
