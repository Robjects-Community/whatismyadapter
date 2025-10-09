<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Exception;

/**
 * CacheService - Redis-based caching for products and junction tables
 *
 * Provides optimized caching for search queries, statistics, and frequently
 * accessed data with proper cache invalidation strategies.
 */
class CacheService
{
    use LogTrait;

    private const CACHE_CONFIG = 'default';
    private const DEFAULT_DURATION = '1 hour';
    private const SEARCH_DURATION = '30 minutes';
    private const STATS_DURATION = '15 minutes';

    /**
     * Cache a search query result
     */
    public function cacheSearch(string $cacheKey, callable $dataCallback, array $options = []): mixed
    {
        $duration = $options['duration'] ?? self::SEARCH_DURATION;
        $cached = Cache::read($cacheKey, self::CACHE_CONFIG);

        if ($cached !== null) {
            $this->log("Cache hit for search: {$cacheKey}", 'debug');

            return $cached;
        }

        $data = $dataCallback();
        Cache::write($cacheKey, $data, self::CACHE_CONFIG, $duration);

        $this->log("Cached search result: {$cacheKey}", 'debug');

        return $data;
    }

    /**
     * Cache product statistics
     */
    public function cacheStats(string $statsType, callable $statsCallback): array
    {
        $cacheKey = "stats_{$statsType}";

        return $this->cacheSearch($cacheKey, $statsCallback, [
            'duration' => self::STATS_DURATION,
        ]);
    }

    /**
     * Cache junction table data
     */
    public function cacheJunctionData(string $table, string $method, array $params, callable $dataCallback): mixed
    {
        $cacheKey = $this->buildJunctionCacheKey($table, $method, $params);

        return $this->cacheSearch($cacheKey, $dataCallback, [
            'duration' => self::DEFAULT_DURATION,
        ]);
    }

    /**
     * Invalidate caches for a specific table
     */
    public function invalidateTableCache(string $table): void
    {
        $patterns = [
            "search_{$table}_*",
            "stats_{$table}_*",
            "junction_{$table}_*",
            "{$table}_categories",
            "{$table}_families",
            "{$table}_brands_*",
        ];

        foreach ($patterns as $pattern) {
            $this->clearCachePattern($pattern);
        }

        $this->log("Invalidated cache for table: {$table}", 'info');
    }

    /**
     * Invalidate all product-related caches
     */
    public function invalidateProductCaches(): void
    {
        $tables = ['products', 'cable_capabilities', 'port_types', 'device_compatibility', 'physical_specs'];

        foreach ($tables as $table) {
            $this->invalidateTableCache($table);
        }

        // Clear general product caches
        Cache::clear(self::CACHE_CONFIG);

        $this->log('Invalidated all product-related caches', 'info');
    }

    /**
     * Cache advanced search results with filters
     */
    public function cacheAdvancedSearch(string $baseKey, array $filters, callable $searchCallback): mixed
    {
        $filterHash = md5(serialize($filters));
        $cacheKey = "search_{$baseKey}_{$filterHash}";

        return $this->cacheSearch($cacheKey, $searchCallback);
    }

    /**
     * Cache category/family/brand listings
     */
    public function cacheListings(string $type, callable $listingCallback): array
    {
        $cacheKey = "{$type}_listings";

        return $this->cacheSearch($cacheKey, $listingCallback, [
            'duration' => self::DEFAULT_DURATION,
        ]);
    }

    /**
     * Get cached data with fallback
     */
    public function getWithFallback(
        string $cacheKey,
        callable $fallbackCallback,
        string $duration = self::DEFAULT_DURATION,
    ): mixed {
        $cached = Cache::read($cacheKey, self::CACHE_CONFIG);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $data = $fallbackCallback();
            Cache::write($cacheKey, $data, self::CACHE_CONFIG, $duration);

            return $data;
        } catch (Exception $e) {
            $this->log("Cache fallback failed for {$cacheKey}: " . $e->getMessage(), 'error');

            return null;
        }
    }

    /**
     * Warm up frequently used caches
     */
    public function warmUpCaches(): void
    {
        $this->log('Starting cache warmup process', 'info');

        // This would typically be called via a console command
        // and warm up the most frequently accessed data

        $warmupTasks = [
            'product_stats' => fn() => $this->warmupProductStats(),
            'capability_categories' => fn() => $this->warmupCapabilityCategories(),
            'port_families' => fn() => $this->warmupPortFamilies(),
            'device_categories' => fn() => $this->warmupDeviceCategories(),
        ];

        foreach ($warmupTasks as $task => $callback) {
            try {
                $callback();
                $this->log("Warmed up cache: {$task}", 'debug');
            } catch (Exception $e) {
                $this->log("Failed to warm up {$task}: " . $e->getMessage(), 'error');
            }
        }

        $this->log('Cache warmup completed', 'info');
    }

    /**
     * Get cache usage statistics
     */
    public function getCacheStats(): array
    {
        // Redis-specific stats would go here
        // This is a simplified version
        return [
            'enabled' => Cache::getConfig(self::CACHE_CONFIG) !== null,
            'config' => self::CACHE_CONFIG,
            'default_duration' => self::DEFAULT_DURATION,
            'search_duration' => self::SEARCH_DURATION,
            'stats_duration' => self::STATS_DURATION,
        ];
    }

    /**
     * Build cache key for junction table operations
     */
    private function buildJunctionCacheKey(string $table, string $method, array $params): string
    {
        $paramHash = md5(serialize($params));

        return "junction_{$table}_{$method}_{$paramHash}";
    }

    /**
     * Clear cache entries matching a pattern
     */
    private function clearCachePattern(string $pattern): void
    {
        // For Redis, this would use SCAN or similar
        // This is a simplified version
        try {
            $engine = Cache::pool(self::CACHE_CONFIG)->getEngine();
            if (method_exists($engine, 'clearGroup')) {
                $engine->clearGroup($pattern);
            }
        } catch (Exception $e) {
            $this->log("Failed to clear cache pattern {$pattern}: " . $e->getMessage(), 'warning');
        }
    }

    /**
     * Warmup methods for specific data types
     */
    private function warmupProductStats(): void
    {
        // Would load ProductsTable and cache common statistics
        Cache::write('stats_products_general', [
            'total' => 0, // Would fetch real data
            'published' => 0,
            'featured' => 0,
        ], self::CACHE_CONFIG, self::STATS_DURATION);
    }

    /**
     * Warmup cache for capability categories data
     */
    private function warmupCapabilityCategories(): void
    {
        Cache::write('capability_categories', [], self::CACHE_CONFIG, self::DEFAULT_DURATION);
    }

    /**
     * Warmup cache for port families data
     */
    private function warmupPortFamilies(): void
    {
        Cache::write('port_families', [], self::CACHE_CONFIG, self::DEFAULT_DURATION);
    }

    /**
     * Warmup cache for device categories data
     */
    private function warmupDeviceCategories(): void
    {
        Cache::write('device_categories', [], self::CACHE_CONFIG, self::DEFAULT_DURATION);
    }
}
