<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'device_types' table.
 *
 * Provides accessors for each column in the 'device_types' schema.
 */
class DeviceTypeDecorator
{
    /**
     * The device type record.
     *
     * @var object|null
     */
    private $deviceType;

    /**
     * DeviceTypeDecorator constructor.
     *
     * @param object|null $deviceType A stdClass representing a 'device_types' row, or null.
     */
    public function __construct($deviceType)
    {
        $this->deviceType = $deviceType;
    }

    /**
     * Get device type ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->deviceType->ID) ? null : (int)$this->deviceType->ID;
    }

    /**
     * Get device type name.
     *
     * @return string
     */
    public function getName()
    {
        return empty($this->deviceType->name) ? '' : $this->deviceType->name;
    }
}
