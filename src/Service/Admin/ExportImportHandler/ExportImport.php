<?php

namespace WP_Statistics\Service\Admin\ExportImportHandler;

use Exception;
use InvalidArgumentException;

/**
 * Handles export and import operations using different drivers.
 */
class ExportImport
{
    /**
     * @var object|null The current driver instance.
     */
    protected $driver = null;

    /**
     * @var array|null Available drivers.
     */
    protected $drivers = null;

    /**
     * Constructor.
     *
     * @param string $driver The driver key to use.
     * @throws InvalidArgumentException When the driver is invalid or not found.
     */
    public function __construct(string $driver)
    {
        $this->driver = $this->getDriver($driver);

        if ($this->driver === null) {
            throw new InvalidArgumentException(
                sprintf(__('Invalid driver: %s', 'wp-statistics'), $driver)
            );
        }
    }

    /**
     * Get a driver instance by key.
     *
     * @param string $driver The driver key.
     * @return object|null The driver instance or null if not found.
     */
    protected function getDriver(string $driver): ?object
    {
        $drivers = apply_filters('wp_statistics_exporter_importer_drivers', $this->drivers);

        return $drivers[$driver] ?? null;
    }

    /**
     * Perform import using the current driver.
     *
     * @return mixed
     * @throws Exception If the driver doesn't support import.
     */
    public function import()
    {
        if (!method_exists($this->driver, 'import')) {
            throw new Exception(
                __('The current driver does not support import operations', 'wp-statistics')
            );
        }

        return $this->driver->import();
    }

    /**
     * Perform export using the current driver.
     *
     * @return mixed
     * @throws Exception If the driver doesn't support export.
     */
    public function export()
    {
        if (!method_exists($this->driver, 'export')) {
            throw new Exception(
                __('The current driver does not support export operations', 'wp-statistics')
            );
        }

        return $this->driver->export();
    }
}