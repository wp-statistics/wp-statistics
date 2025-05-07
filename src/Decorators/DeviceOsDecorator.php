<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'device_oss' table.
 *
 * Provides accessors for each column in the 'device_oss' schema.
 */
class DeviceOsDecorator
{
    /**
     * The device OS record.
     *
     * @var object|null
     */
    private $deviceOs;

    /**
     * DeviceOsDecorator constructor.
     *
     * @param object|null $deviceOs A stdClass representing a 'device_oss' row, or null.
     */
    public function __construct($deviceOs)
    {
        $this->deviceOs = $deviceOs;
    }

    /**
     * Get the device OS ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->deviceOs->ID) ? null : (int)$this->deviceOs->ID;
    }

    /**
     * Get the device OS name.
     *
     * @return string
     */
    public function getName()
    {
        return empty($this->deviceOs->name) ? '' : $this->deviceOs->name;
    }
}
