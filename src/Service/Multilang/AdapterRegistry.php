<?php

namespace WP_Statistics\Service\Multilang;

use WP_Statistics\Service\Multilang\Adapters\AdapterInterface;
use WP_Statistics\Service\Multilang\Adapters\PolylangAdapter;
use WP_Statistics\Service\Multilang\Adapters\QTranslateAdapter;
use WP_Statistics\Service\Multilang\Adapters\TranslatePressAdapter;
use WP_Statistics\Service\Multilang\Adapters\WeglotAdapter;
use WP_Statistics\Service\Multilang\Adapters\WpmlAdapter;

/**
 * Discovers and selects the active multi-language adapter.
 *
 * Adapter list is the priority order used when more than one multi-language
 * plugin is installed. Adapters are stored as class names and instantiated
 * lazily so the typical (no plugin) request constructs zero adapter objects.
 *
 * The wp_statistics_multilang_adapter filter lets a site override the selection.
 *
 * @since 15.x
 */
class AdapterRegistry
{
    /**
     * Default priority order. First adapter whose isActive() returns true wins.
     *
     * @var string[]
     */
    private static $defaultClasses = [
        WpmlAdapter::class,
        PolylangAdapter::class,
        TranslatePressAdapter::class,
        QTranslateAdapter::class,
        WeglotAdapter::class,
    ];

    /**
     * Each entry is either a class-string (lazy) or a pre-built AdapterInterface
     * instance (test injection).
     *
     * @var array<int, string|AdapterInterface>
     */
    private $adapters;

    /**
     * @param array<int, string|AdapterInterface>|null $adapters Optional override (used by tests).
     */
    public function __construct(?array $adapters = null)
    {
        $this->adapters = $adapters ?? self::$defaultClasses;
    }

    /**
     * Materialize all adapters as instances. Used by tests / introspection.
     *
     * @return AdapterInterface[]
     */
    public function getAdapters(): array
    {
        $resolved = [];
        foreach ($this->adapters as $entry) {
            $resolved[] = is_string($entry) ? new $entry() : $entry;
        }
        return $resolved;
    }

    /**
     * Pick the adapter to use for the current request, honoring the override filter.
     */
    public function resolve(): ?AdapterInterface
    {
        $resolved = null;

        foreach ($this->adapters as $entry) {
            $adapter = is_string($entry) ? new $entry() : $entry;
            if ($adapter->isActive()) {
                $resolved = $adapter;
                break;
            }
        }

        /**
         * Override the auto-detected multi-language adapter.
         *
         * Return an AdapterInterface instance to take over, or null to disable
         * multi-language tracking entirely for this request. Returning anything
         * else is ignored.
         *
         * @param AdapterInterface|null $resolved The adapter the registry chose.
         * @since 15.x
         */
        $override = apply_filters('wp_statistics_multilang_adapter', $resolved);

        if ($override === null) {
            return null;
        }

        return $override instanceof AdapterInterface ? $override : null;
    }
}
