<?php

namespace WP_Statistics\Service\Admin\ExportImport;

use Exception;
use InvalidArgumentException;
use WP_REST_Request;

/**
 * Handles export and import operations using different drivers.
 */
class ExportImportHandler
{
    /**
     * @var object|null The current driver instance.
     */
    protected $driver = null;

    /**
     * @var array Available drivers.
     */
    protected $drivers = [];

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
                sprintf(esc_html__('Invalid driver: %s', 'wp-statistics'), $driver)
            );
        }
    }

    /**
     * Get a driver instance by key.
     *
     * @param string $driver The driver key.
     * @return object|null The driver instance or null if not found.
     */
    protected function getDriver(string $driver)
    {
        $drivers = apply_filters('wp_statistics_exporter_importer_drivers', $this->drivers);

        if (!isset($drivers[$driver])) {
            return null;
        }

        $class = $drivers[$driver];


        if (!class_exists($class)) {
            return null;
        }

        static $instances = [];

        if (!isset($instances[$driver])) {
            $instances[$driver] = new $class();
        }

        return $instances[$driver];
    }

    /**
     * Perform import using the current driver.
     *
     * @param WP_REST_Request $request
     *
     * @return mixed
     * @throws Exception If the driver doesn't support import.
     */
    public function import(WP_REST_Request $request)
    {
        if (!method_exists($this->driver, 'import')) {
            throw new Exception(
                esc_html__('The current driver does not support import operations', 'wp-statistics')
            );
        }

        return $this->driver->import($request);
    }

    /**
     * Perform export using the current driver.
     *
     * @param WP_REST_Request $request
     *
     * @return mixed
     * @throws Exception If the driver doesn't support export.
     */
    public function export(WP_REST_Request $request)
    {
        if (!method_exists($this->driver, 'export')) {
            throw new Exception(
                esc_html__('The current driver does not support export operations', 'wp-statistics')
            );
        }

        return $this->driver->export($request);
    }
}