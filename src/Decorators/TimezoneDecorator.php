<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'timezones' table.
 *
 * Provides accessors for each column in the 'timezones' schema.
 */
class TimezoneDecorator
{
    /**
     * The timezone record.
     *
     * @var object|null
     */
    private $timezone;

    /**
     * TimezoneDecorator constructor.
     *
     * @param object|null $timezone A stdClass representing a 'timezones' row, or null.
     */
    public function __construct($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Get the timezone ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->timezone->ID) ? null : (int)$this->timezone->ID;
    }

    /**
     * Get the timezone name (e.g., "America/New_York").
     *
     * @return string
     */
    public function getName()
    {
        return empty($this->timezone->name) ? '' : $this->timezone->name;
    }

    /**
     * Get the offset string (e.g., "+03:00").
     *
     * @return string
     */
    public function getOffset()
    {
        return empty($this->timezone->offset) ? '' : $this->timezone->offset;
    }

    /**
     * Determine if this is a DST timezone.
     *
     * @return bool
     */
    public function isDst()
    {
        return !empty($this->timezone->is_dst);
    }
}
