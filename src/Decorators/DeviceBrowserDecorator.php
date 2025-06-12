<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;

/**
 * Decorator for a record from the 'device_browsers' table.
 *
 * Provides accessors for each column in the 'device_browsers' schema.
 */
class DeviceBrowserDecorator
{
    /**
     * The device browser record.
     *
     * @var object|null
     */
    private $deviceBrowser;

    /**
     * DeviceBrowserDecorator constructor.
     *
     * @param object|null $deviceBrowser A stdClass representing a 'device_browsers' row, or null.
     */
    public function __construct($deviceBrowser)
    {
        $this->deviceBrowser = $deviceBrowser;
    }

    /**
     * Get the device browser ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->deviceBrowser->ID) ? null : (int)$this->deviceBrowser->ID;
    }

    /**
     * Get the device browser name.
     *
     * @return string
     */
    public function getName()
    {
        return empty($this->deviceBrowser->name) ? '' : $this->deviceBrowser->name;
    }

    /**
     * Get the browser logo URL.
     *
     * @return string
     */
    public function getLogo()
    {
        return DeviceHelper::getBrowserLogo($this->getName());
    }

    /**
     * Get the raw browser name.
     *
     * @return string
     */
    public function getRaw()
    {
        return \WP_STATISTICS\Admin_Template::unknownToNotSet($this->getName()) ?? null;
    }
}
