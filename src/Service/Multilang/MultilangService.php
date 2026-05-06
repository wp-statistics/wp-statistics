<?php

namespace WP_Statistics\Service\Multilang;

use WP_Statistics\Service\Multilang\Adapters\AdapterInterface;

/**
 * Public entry point for multi-language plugin support.
 *
 * Hides the AdapterRegistry and adapter chain from callers and provides:
 *   - per-request memoization of detection (one adapter call per request for
 *     per-request adapters; one per resource for per-post adapters),
 *   - a friendly fallback for label lookup when no plugin is active,
 *   - a singleton hook so static call sites (ResourceResolver) can reach the
 *     service without DI plumbing, while tests can swap in a stub via setInstance().
 *
 * @since 15.x
 */
class MultilangService
{
    /** @var self|null */
    private static $instance = null;

    /** @var AdapterRegistry */
    private $registry;

    /** @var bool */
    private $resolved = false;

    /** @var AdapterInterface|null */
    private $adapter = null;

    /** @var array<string, string|null> */
    private $detectionCache = [];

    public function __construct(?AdapterRegistry $registry = null)
    {
        $this->registry = $registry ?? new AdapterRegistry();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Drop the cached singleton so the next getInstance() call rebuilds. For tests.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    public function getActiveAdapter(): ?AdapterInterface
    {
        if (!$this->resolved) {
            $this->adapter  = $this->registry->resolve();
            $this->resolved = true;
        }
        return $this->adapter;
    }

    public function isActive(): bool
    {
        return $this->getActiveAdapter() !== null;
    }

    public function getMode(): ?string
    {
        $adapter = $this->getActiveAdapter();
        return $adapter ? $adapter->getMode() : null;
    }

    /**
     * Resolve the language for a hit. Memoization is mode-aware:
     *   - per-request adapters return the same value for every hit in a request,
     *     so we cache once.
     *   - per-post adapters key by (resource_type, resource_id) — the URI is
     *     redundant since per-post identity is stable across URIs.
     */
    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        $adapter = $this->getActiveAdapter();
        if ($adapter === null) {
            return null;
        }

        $key = $adapter->getMode() === AdapterInterface::MODE_PER_REQUEST
            ? '__per_request__'
            : $resourceType . '|' . $resourceId;

        if (!array_key_exists($key, $this->detectionCache)) {
            $this->detectionCache[$key] = $adapter->detectLanguage($resourceType, $resourceId, $uri);
        }

        return $this->detectionCache[$key];
    }

    /**
     * Whether language should be part of the (resource_id, resource_type, language)
     * identity tuple when looking up / inserting a resource row.
     *
     * True when:
     *   - active adapter is per-request (TRP, qTranslate, WeGlot), OR
     *   - the resource has no underlying post (home, search, 404, archives) —
     *     these have resource_id = 0 so the same row would otherwise collapse
     *     hits across all languages into a single bucket.
     */
    public function languageIsIdentity(int $resourceId): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->getMode() === AdapterInterface::MODE_PER_REQUEST) {
            return true;
        }

        return $resourceId === 0;
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableLanguages(): array
    {
        $adapter = $this->getActiveAdapter();
        return $adapter ? $adapter->getAvailableLanguages() : [];
    }

    /**
     * Languages that should appear as filter options in the analytics UI.
     *
     * Unions:
     *   - the active adapter's reported languages (if any), and
     *   - DISTINCT language codes from the resources table.
     *
     * Adapter labels take priority for codes the adapter knows about; DB-only
     * codes (historical data from a previously-installed multilang plugin, or
     * codes the current adapter no longer exposes) get labels via the built-in
     * LanguageNames table. Soft-deleted resource rows and empty-language rows
     * are excluded.
     *
     * @return array<string, string> Map of language code → human label, sorted by code.
     */
    public function getFilterableLanguages(): array
    {
        $adapter = $this->getActiveAdapter();
        $result  = $adapter ? $adapter->getAvailableLanguages() : [];

        global $wpdb;
        $table = $wpdb->prefix . 'statistics_resources';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $codes = $wpdb->get_col(
            "SELECT DISTINCT language FROM `{$table}` "
            . "WHERE language != '' AND is_deleted = 0"
        );

        if (is_array($codes)) {
            foreach ($codes as $code) {
                if (!is_string($code) || $code === '') {
                    continue;
                }
                if (!isset($result[$code])) {
                    $result[$code] = LanguageNames::lookup($code);
                }
            }
        }

        ksort($result);
        return $result;
    }

    /**
     * Human label for a stored language code. Falls back to a built-in
     * common-names table (and finally to the code itself) when no adapter is
     * active or the code is unknown — so dashboards still render gracefully
     * after the plugin is removed.
     */
    public function getLanguageLabel(string $code): string
    {
        if ($code === '') {
            return '';
        }

        $adapter = $this->getActiveAdapter();
        if ($adapter !== null) {
            $available = $adapter->getAvailableLanguages();
            if (isset($available[$code]) && $available[$code] !== '') {
                return $available[$code];
            }
        }

        return LanguageNames::lookup($code);
    }
}
