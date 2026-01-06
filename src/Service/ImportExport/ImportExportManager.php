<?php

namespace WP_Statistics\Service\ImportExport;

use WP_Statistics\Service\ImportExport\Contracts\ImportAdapterInterface;
use WP_Statistics\Service\ImportExport\Endpoints\ImportExportEndpoints;

/**
 * Import/Export Manager.
 *
 * Central manager for all import/export operations with lazy-loaded adapters.
 * Adapters are only instantiated when actually needed to minimize overhead.
 *
 * @since 15.0.0
 */
class ImportExportManager
{
    /**
     * Registered adapter class names (lazy loading).
     *
     * @var array<string, string>
     */
    private $adapterClasses = [];

    /**
     * Instantiated adapters (on-demand).
     *
     * @var array<string, ImportAdapterInterface>
     */
    private $adapters = [];

    /**
     * AJAX endpoints handler.
     *
     * @var ImportExportEndpoints|null
     */
    private $endpoints = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registerDefaultAdapters();
        $this->registerEndpoints();
    }

    /**
     * Register default adapters.
     *
     * Only stores class names - no instantiation happens here.
     *
     * @return void
     */
    private function registerDefaultAdapters(): void
    {
        $defaultAdapters = [
            'wp_statistics_backup' => Adapters\WpStatisticsBackupAdapter::class,
            'legacy_v14'           => Adapters\LegacyV14Adapter::class,
            'google_analytics_4'   => Adapters\GoogleAnalytics4Adapter::class,
            'plausible'            => Adapters\PlausibleAdapter::class,
        ];

        /**
         * Filter the registered import adapters.
         *
         * Allows addons to register additional adapters.
         *
         * @param array $adapters Array of adapter key => class name
         */
        $this->adapterClasses = apply_filters('wp_statistics_import_adapters', $defaultAdapters);
    }

    /**
     * Register AJAX endpoints.
     *
     * @return void
     */
    private function registerEndpoints(): void
    {
        $this->endpoints = new ImportExportEndpoints($this);
        $this->endpoints->register();
    }

    /**
     * Get an adapter by key.
     *
     * Lazy instantiation - adapter is only created when first requested.
     *
     * @param string $key Adapter key (e.g., 'wp_statistics_backup')
     * @return ImportAdapterInterface
     * @throws \InvalidArgumentException If adapter not found
     */
    public function getAdapter(string $key): ImportAdapterInterface
    {
        if (!isset($this->adapterClasses[$key])) {
            throw new \InvalidArgumentException("Import adapter not found: {$key}");
        }

        // Lazy instantiation
        if (!isset($this->adapters[$key])) {
            $className = $this->adapterClasses[$key];

            if (!class_exists($className)) {
                throw new \RuntimeException("Adapter class not found: {$className}");
            }

            $this->adapters[$key] = new $className();
        }

        return $this->adapters[$key];
    }

    /**
     * Check if an adapter exists.
     *
     * @param string $key Adapter key
     * @return bool
     */
    public function hasAdapter(string $key): bool
    {
        return isset($this->adapterClasses[$key]);
    }

    /**
     * Register a new adapter.
     *
     * @param string $key       Unique adapter key
     * @param string $className Fully qualified class name
     * @return self
     */
    public function registerAdapter(string $key, string $className): self
    {
        $this->adapterClasses[$key] = $className;

        // Clear cached instance if exists
        unset($this->adapters[$key]);

        return $this;
    }

    /**
     * Unregister an adapter.
     *
     * @param string $key Adapter key to remove
     * @return self
     */
    public function unregisterAdapter(string $key): self
    {
        unset($this->adapterClasses[$key], $this->adapters[$key]);
        return $this;
    }

    /**
     * Get all registered adapter keys.
     *
     * @return array<string>
     */
    public function getAdapterKeys(): array
    {
        return array_keys($this->adapterClasses);
    }

    /**
     * Get adapter metadata for UI.
     *
     * Returns minimal info for all adapters without full instantiation.
     *
     * @return array<string, array>
     */
    public function getAdaptersMetadata(): array
    {
        $metadata = [];

        foreach ($this->adapterClasses as $key => $className) {
            try {
                $adapter = $this->getAdapter($key);
                $metadata[$key] = [
                    'key'                 => $key,
                    'name'                => $adapter->getName(),
                    'label'               => $adapter->getLabel(),
                    'extensions'          => $adapter->getSupportedExtensions(),
                    'required_columns'    => $adapter->getRequiredColumns(),
                    'optional_columns'    => $adapter->getOptionalColumns(),
                    'is_aggregate_import' => $adapter->isAggregateImport(),
                    'target_tables'       => $adapter->getTargetTables(),
                ];
            } catch (\Exception $e) {
                // Skip adapters that fail to load
                continue;
            }
        }

        return $metadata;
    }

    /**
     * Get the exporter instance.
     *
     * @return Export\WpStatisticsExporter
     */
    public function getExporter(): Export\WpStatisticsExporter
    {
        return new Export\WpStatisticsExporter();
    }

    /**
     * Clear all cached adapter instances.
     *
     * Useful for long-running processes to free memory.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->adapters = [];
    }
}
