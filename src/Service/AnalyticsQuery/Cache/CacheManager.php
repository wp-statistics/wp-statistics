<?php

namespace WP_Statistics\Service\AnalyticsQuery\Cache;

/**
 * Manages query result caching.
 *
 * Uses WordPress transients for caching query results with configurable TTL
 * based on query type and date range.
 *
 * @since 15.0.0
 */
class CacheManager
{
    /**
     * Cache key prefix.
     *
     * @var string
     */
    private const CACHE_PREFIX = 'wp_statistics_analytics_';

    /**
     * Default cache TTL in seconds.
     *
     * @var int
     */
    private const DEFAULT_TTL = 1800; // 30 minutes

    /**
     * Cache TTL settings based on query characteristics.
     *
     * @var array
     */
    private static $ttlSettings = [
        'realtime'   => 60,      // 1 minute for real-time data
        'today'      => 300,     // 5 minutes for today's data
        'week'       => 900,     // 15 minutes for last 7 days
        'month'      => 1800,    // 30 minutes for last 30 days
        'historical' => 7200,    // 2 hours for historical data (30+ days)
    ];

    /**
     * Whether caching is enabled.
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * Constructor.
     *
     * @param bool $enabled Whether caching is enabled.
     */
    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get cached result for a query.
     *
     * @param array $request Query request.
     * @return array|null Cached result or null if not found.
     */
    public function get(array $request): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $key    = $this->generateKey($request);
        $cached = get_transient($key);

        if ($cached === false) {
            return null;
        }

        return $cached;
    }

    /**
     * Store result in cache.
     *
     * @param array $request Query request.
     * @param array $result  Query result.
     * @return bool Success status.
     */
    public function set(array $request, array $result): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $key = $this->generateKey($request);
        $ttl = $this->calculateTTL($request);

        // Add cache metadata to result
        $result['_cache'] = [
            'cached_at' => time(),
            'ttl'       => $ttl,
        ];

        return set_transient($key, $result, $ttl);
    }

    /**
     * Delete cached result for a query.
     *
     * @param array $request Query request.
     * @return bool Success status.
     */
    public function delete(array $request): bool
    {
        $key = $this->generateKey($request);
        return delete_transient($key);
    }

    /**
     * Clear all analytics cache.
     *
     * @return int Number of entries cleared.
     */
    public function clearAll(): int
    {
        global $wpdb;

        $pattern = '_transient_' . self::CACHE_PREFIX . '%';
        $count   = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );

        // Also delete timeout entries
        $timeoutPattern = '_transient_timeout_' . self::CACHE_PREFIX . '%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $timeoutPattern
            )
        );

        return $count;
    }

    /**
     * Generate a unique cache key for a request.
     *
     * @param array $request Query request.
     * @return string Cache key.
     */
    private function generateKey(array $request): string
    {
        // Normalize request for consistent key generation
        $normalized = [
            'sources'   => $request['sources'] ?? [],
            'group_by'  => $request['group_by'] ?? [],
            'filters'   => $request['filters'] ?? [],
            'date_from' => $request['date_from'] ?? '',
            'date_to'   => $request['date_to'] ?? '',
            'compare'   => $request['compare'] ?? false,
            'page'      => $request['page'] ?? 1,
            'per_page'  => $request['per_page'] ?? 10,
            'order_by'  => $request['order_by'] ?? '',
            'order'     => $request['order'] ?? 'DESC',
        ];

        sort($normalized['sources']);
        sort($normalized['group_by']);
        ksort($normalized['filters']);

        $hash = md5(wp_json_encode($normalized));

        return self::CACHE_PREFIX . $hash;
    }

    /**
     * Calculate cache TTL based on query characteristics.
     *
     * @param array $request Query request.
     * @return int TTL in seconds.
     */
    private function calculateTTL(array $request): int
    {
        // Check for real-time sources
        $sources = $request['sources'] ?? [];
        if (in_array('online_visitors', $sources)) {
            return self::$ttlSettings['realtime'];
        }

        // Calculate based on date range
        $dateFrom = $request['date_from'] ?? null;
        $dateTo   = $request['date_to'] ?? null;

        if (!$dateFrom || !$dateTo) {
            return self::DEFAULT_TTL;
        }

        $today    = date('Y-m-d');
        $to       = new \DateTime($dateTo);
        $todayObj = new \DateTime($today);

        // If range includes today
        if ($dateTo === $today || $to >= $todayObj) {
            return self::$ttlSettings['today'];
        }

        // Calculate how old the data is
        $daysAgo = $todayObj->diff($to)->days;

        if ($daysAgo <= 7) {
            return self::$ttlSettings['week'];
        } elseif ($daysAgo <= 30) {
            return self::$ttlSettings['month'];
        }

        return self::$ttlSettings['historical'];
    }

    /**
     * Get cache TTL for a request (without storing).
     *
     * @param array $request Query request.
     * @return int TTL in seconds.
     */
    public function getTTL(array $request): int
    {
        return $this->calculateTTL($request);
    }

    /**
     * Check if a cached result is still valid.
     *
     * @param array $result Cached result with metadata.
     * @return bool True if valid.
     */
    public function isValid(array $result): bool
    {
        if (!isset($result['_cache'])) {
            return true; // No cache metadata, assume valid
        }

        $cachedAt = $result['_cache']['cached_at'] ?? 0;
        $ttl      = $result['_cache']['ttl'] ?? self::DEFAULT_TTL;

        return (time() - $cachedAt) < $ttl;
    }

    /**
     * Enable or disable caching.
     *
     * @param bool $enabled Whether caching is enabled.
     * @return self
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Check if caching is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Generate a batch cache key for multiple queries.
     *
     * @param array $queries Array of query requests.
     * @return string Cache key.
     */
    public function generateBatchKey(array $queries): string
    {
        $keys = [];
        foreach ($queries as $query) {
            $keys[] = $this->generateKey($query);
        }

        sort($keys);
        return self::CACHE_PREFIX . 'batch_' . md5(implode('|', $keys));
    }
}
