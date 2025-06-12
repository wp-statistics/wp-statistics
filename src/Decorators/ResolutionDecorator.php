<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'resolutions' table.
 *
 * Provides accessors for each column in the 'resolutions' schema.
 */
class ResolutionDecorator
{
    /**
     * The resolution record.
     *
     * @var object|null
     */
    private $resolution;

    /**
     * ResolutionDecorator constructor.
     *
     * @param object|null $resolution A stdClass representing a 'resolutions' row, or null.
     */
    public function __construct($resolution)
    {
        $this->resolution = $resolution;
    }

    /**
     * Get the resolution ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->resolution->ID) ? null : (int)$this->resolution->ID;
    }

    /**
     * Get the resolution width in pixels.
     *
     * @return int|null
     */
    public function getWidth()
    {
        return empty($this->resolution->width) ? null : (int)$this->resolution->width;
    }

    /**
     * Get the resolution height in pixels.
     *
     * @return int|null
     */
    public function getHeight()
    {
        return empty($this->resolution->height) ? null : (int)$this->resolution->height;
    }
}
