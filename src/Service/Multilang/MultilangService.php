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
