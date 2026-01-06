<?php

namespace WP_Statistics\Service\Admin\Diagnostic;

use WP_Statistics\Traits\TransientCacheTrait;
use WP_Statistics\Service\Admin\Diagnostic\Checks\CheckInterface;
use WP_Statistics\Service\Admin\Diagnostic\Checks\LoopbackCheck;
use WP_Statistics\Service\Admin\Diagnostic\Checks\GeoIpCheck;
use WP_Statistics\Service\Admin\Diagnostic\Checks\TrackingCheck;
use WP_Statistics\Service\Admin\Diagnostic\Checks\ServerEnvironmentCheck;
use WP_Statistics\Service\Admin\Diagnostic\Checks\CronCheck;
use WP_Statistics\Service\Admin\Diagnostic\Checks\CachePluginCheck;
use WP_Statistics\Service\Admin\Diagnostic\Checks\SchemaCheck;

/**
 * Diagnostic Manager.
 *
 * Orchestrates diagnostic checks and manages result caching.
 *
 * @since 15.0.0
 */
class DiagnosticManager
{
    use TransientCacheTrait;

    /**
     * Cache key for lightweight checks.
     */
    private const CACHE_KEY_LIGHTWEIGHT = 'diagnostic_lightweight';

    /**
     * Cache key for full (all) checks.
     */
    private const CACHE_KEY_FULL = 'diagnostic_full';

    /**
     * Cache TTL for lightweight checks (12 hours).
     */
    private const CACHE_TTL_LIGHTWEIGHT = 12 * HOUR_IN_SECONDS;

    /**
     * Cache TTL for full checks (24 hours).
     */
    private const CACHE_TTL_FULL = 24 * HOUR_IN_SECONDS;

    /**
     * Registered check classes (key => class name).
     *
     * @var array<string, class-string<CheckInterface>>
     */
    private array $checkClasses = [
        'geoip'    => GeoIpCheck::class,
        'server'   => ServerEnvironmentCheck::class,
        'cron'     => CronCheck::class,
        'cache'    => CachePluginCheck::class,
        'loopback' => LoopbackCheck::class,
        'tracking' => TrackingCheck::class,
        'schema'   => SchemaCheck::class,
    ];

    /**
     * Instantiated check objects (lazy loaded).
     *
     * @var array<string, CheckInterface>
     */
    private array $checks = [];

    /**
     * Get all registered check keys.
     *
     * @return string[]
     */
    public function getCheckKeys(): array
    {
        return array_keys($this->checkClasses);
    }

    /**
     * Get only lightweight check keys.
     *
     * @return string[]
     */
    public function getLightweightCheckKeys(): array
    {
        $keys = [];
        foreach ($this->checkClasses as $key => $class) {
            $check = $this->getCheck($key);
            if ($check && $check->isLightweight()) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * Get only heavy check keys.
     *
     * @return string[]
     */
    public function getHeavyCheckKeys(): array
    {
        $keys = [];
        foreach ($this->checkClasses as $key => $class) {
            $check = $this->getCheck($key);
            if ($check && !$check->isLightweight()) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * Check if a check exists.
     *
     * @param string $key Check key.
     * @return bool
     */
    public function hasCheck(string $key): bool
    {
        return isset($this->checkClasses[$key]);
    }

    /**
     * Get a check instance (lazy loaded).
     *
     * @param string $key Check key.
     * @return CheckInterface|null
     */
    public function getCheck(string $key): ?CheckInterface
    {
        if (!$this->hasCheck($key)) {
            return null;
        }

        if (!isset($this->checks[$key])) {
            $class = $this->checkClasses[$key];
            $this->checks[$key] = new $class();
        }

        return $this->checks[$key];
    }

    /**
     * Register a custom check class.
     *
     * @param string                       $key   Check key.
     * @param class-string<CheckInterface> $class Check class name.
     * @return self
     */
    public function registerCheck(string $key, string $class): self
    {
        $this->checkClasses[$key] = $class;
        unset($this->checks[$key]); // Clear cached instance
        return $this;
    }

    /**
     * Run all checks.
     *
     * @param bool $fresh Force fresh results (ignore cache).
     * @return DiagnosticResult[]
     */
    public function runAll(bool $fresh = false): array
    {
        if (!$fresh) {
            $cached = $this->getCachedResult(self::CACHE_KEY_FULL);
            if ($cached !== false && is_array($cached)) {
                return $this->hydrateResults($cached);
            }
        }

        $results = [];
        foreach ($this->checkClasses as $key => $class) {
            $results[$key] = $this->runCheck($key);
        }

        $this->setCachedResult(self::CACHE_KEY_FULL, $this->serializeResults($results), self::CACHE_TTL_FULL);

        return $results;
    }

    /**
     * Run only lightweight checks.
     *
     * @param bool $fresh Force fresh results (ignore cache).
     * @return DiagnosticResult[]
     */
    public function runLightweight(bool $fresh = false): array
    {
        if (!$fresh) {
            $cached = $this->getCachedResult(self::CACHE_KEY_LIGHTWEIGHT);
            if ($cached !== false && is_array($cached)) {
                return $this->hydrateResults($cached);
            }
        }

        $results = [];
        foreach ($this->getLightweightCheckKeys() as $key) {
            $results[$key] = $this->runCheck($key);
        }

        $this->setCachedResult(self::CACHE_KEY_LIGHTWEIGHT, $this->serializeResults($results), self::CACHE_TTL_LIGHTWEIGHT);

        return $results;
    }

    /**
     * Run a single check.
     *
     * @param string $key Check key.
     * @return DiagnosticResult|null
     */
    public function runCheck(string $key): ?DiagnosticResult
    {
        $check = $this->getCheck($key);
        if (!$check) {
            return null;
        }

        try {
            return $check->run();
        } catch (\Throwable $e) {
            return DiagnosticResult::fail(
                $key,
                $check->getLabel(),
                sprintf(__('Check failed with error: %s', 'wp-statistics'), $e->getMessage()),
                ['exception' => get_class($e)],
                $check->getHelpUrl()
            );
        }
    }

    /**
     * Get cached results (lightweight + last full if available).
     *
     * @return DiagnosticResult[]
     */
    public function getResults(): array
    {
        // Get lightweight results (auto-run if needed)
        $lightweight = $this->runLightweight();

        // Get cached full results (for heavy checks)
        $fullCached = $this->getCachedResult(self::CACHE_KEY_FULL);
        $heavy = [];

        if ($fullCached !== false && is_array($fullCached)) {
            $fullResults = $this->hydrateResults($fullCached);
            foreach ($this->getHeavyCheckKeys() as $key) {
                if (isset($fullResults[$key])) {
                    $heavy[$key] = $fullResults[$key];
                }
            }
        }

        return array_merge($lightweight, $heavy);
    }

    /**
     * Get only failed checks from results.
     *
     * @return DiagnosticResult[]
     */
    public function getFailedChecks(): array
    {
        $results = $this->getResults();
        return array_filter($results, fn(DiagnosticResult $r) => $r->isFailed());
    }

    /**
     * Get only warning checks from results.
     *
     * @return DiagnosticResult[]
     */
    public function getWarningChecks(): array
    {
        $results = $this->getResults();
        return array_filter($results, fn(DiagnosticResult $r) => $r->isWarning());
    }

    /**
     * Check if there are any failures.
     *
     * @return bool
     */
    public function hasFailures(): bool
    {
        return !empty($this->getFailedChecks());
    }

    /**
     * Check if there are any warnings or failures.
     *
     * @return bool
     */
    public function hasIssues(): bool
    {
        return $this->hasFailures() || !empty($this->getWarningChecks());
    }

    /**
     * Get timestamp of last full check.
     *
     * @return int|null
     */
    public function getLastFullCheckTime(): ?int
    {
        $cached = $this->getCachedResult(self::CACHE_KEY_FULL);
        if ($cached !== false && is_array($cached) && !empty($cached)) {
            $first = reset($cached);
            return $first['timestamp'] ?? null;
        }
        return null;
    }

    /**
     * Clear all cached results.
     *
     * @return void
     */
    public function clearCache(): void
    {
        delete_transient('wps_' . self::CACHE_KEY_LIGHTWEIGHT);
        delete_transient('wps_' . self::CACHE_KEY_FULL);
    }

    /**
     * Serialize results for caching.
     *
     * @param DiagnosticResult[] $results Results to serialize.
     * @return array
     */
    private function serializeResults(array $results): array
    {
        return array_map(fn(DiagnosticResult $r) => $r->toArray(), $results);
    }

    /**
     * Hydrate results from cache.
     *
     * @param array $data Cached data.
     * @return DiagnosticResult[]
     */
    private function hydrateResults(array $data): array
    {
        return array_map(fn(array $item) => new DiagnosticResult($item), $data);
    }
}
