<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'device_browser_versions' table.
 *
 * Provides accessors for each column in the 'device_browser_versions' schema.
 */
class DeviceBrowserVersionDecorator
{
    /**
     * The device browser version record.
     *
     * @var object|null
     */
    private $deviceBrowserVersion;

    /**
     * DeviceBrowserVersionDecorator constructor.
     *
     * @param object|null $deviceBrowserVersion A stdClass representing a 'device_browser_versions' row, or null.
     */
    public function __construct($deviceBrowserVersion)
    {
        $this->deviceBrowserVersion = $deviceBrowserVersion;
    }

    /**
     * Get the version ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->deviceBrowserVersion->ID) ? null : (int)$this->deviceBrowserVersion->ID;
    }

    /**
     * Get the browser ID.
     *
     * @return int|null
     */
    public function getBrowserId()
    {
        return empty($this->deviceBrowserVersion->browser_id) ? null : (int)$this->deviceBrowserVersion->browser_id;
    }

    /**
     * Get the version string.
     *
     * @return string
     */
    public function getVersion()
    {
        return empty($this->deviceBrowserVersion->version) ? '' : $this->deviceBrowserVersion->version;
    }
}
