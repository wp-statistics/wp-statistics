<?php

namespace WP_Statistics\Service\Tracking\Core;

use Exception;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;

/**
 * IP-based rate limiter for tracking endpoints.
 *
 * Checks a per-IP request counter stored in object cache (or transients)
 * and throws a 429 exception when the threshold is exceeded.
 * Runs before Payload::parse() so no DB work happens for rate-limited requests.
 *
 * Must work in WordPress SHORTINIT mode.
 *
 * @since 15.1.0
 */
final class RateLimiter
{
    private const CACHE_GROUP = 'wp_statistics_rate_limit';

    /**
     * Check if the current request exceeds the rate limit.
     *
     * @throws Exception With code 429 if rate limit exceeded.
     */
    public static function check(): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $ip  = Ip::getCurrent();
        $key = self::getCacheKey($ip);

        $window = self::getTimeWindow();
        $count  = self::incrementCounter($key, $window);

        if ($count > self::getThreshold()) {
            throw new Exception('Rate limit exceeded', 429);
        }
    }

    /**
     * Whether rate limiting is enabled.
     */
    public static function isEnabled(): bool
    {
        return (bool) Option::getValue('tracker_rate_limit', false);
    }

    /**
     * Get the configured threshold (hits per window).
     */
    private static function getThreshold(): int
    {
        $threshold = (int) Option::getValue('tracker_rate_limit_threshold', 30);

        return (int) apply_filters('wp_statistics_tracker_rate_limit_threshold', $threshold);
    }

    /**
     * Get the time window in seconds.
     */
    public static function getTimeWindow(): int
    {
        return (int) apply_filters('wp_statistics_rate_limit_time_window', 60);
    }

    /**
     * Build the cache key for a given IP.
     */
    private static function getCacheKey(string $ip): string
    {
        return self::CACHE_GROUP . '_' . md5($ip);
    }

    /**
     * Increment and return the hit counter for the given key.
     *
     * Uses wp_cache (object cache) when an external cache backend is active,
     * falls back to transients otherwise.
     */
    private static function incrementCounter(string $key, int $window): int
    {
        if (self::hasObjectCache()) {
            return self::incrementViaObjectCache($key, $window);
        }

        return self::incrementViaTransient($key, $window);
    }

    /**
     * Atomic increment via wp_cache (Redis/Memcached).
     */
    private static function incrementViaObjectCache(string $key, int $window): int
    {
        // wp_cache_add is atomic: no-op if key already exists, avoiding the
        // race where two concurrent requests both see a miss and reset to 1.
        wp_cache_add($key, 0, self::CACHE_GROUP, $window);

        return (int) wp_cache_incr($key, 1, self::CACHE_GROUP);
    }

    /**
     * Increment via transients (DB-backed fallback).
     */
    private static function incrementViaTransient(string $key, int $window): int
    {
        $count = get_transient($key);

        if ($count === false) {
            set_transient($key, 1, $window);
            return 1;
        }

        $count = (int) $count + 1;
        set_transient($key, $count, $window);

        return $count;
    }

    /**
     * Whether an external object cache (Redis, Memcached) is active.
     */
    private static function hasObjectCache(): bool
    {
        return function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache();
    }
}
