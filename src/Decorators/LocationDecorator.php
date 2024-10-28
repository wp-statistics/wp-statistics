<?php

namespace WP_Statistics\Decorators;

use WP_STATISTICS\Country;

class LocationDecorator
{
    private $visitor;

    public function __construct($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Get the country icon URL based on the visitor's location.
     *
     * @return string
     */
    public function getCountryFlag()
    {
        return Country::flag($this->visitor->location);
    }

    /**
     * Get the country name based on the visitor's location.
     *
     * @return string
     */
    public function getCountryName()
    {
        return Country::getName($this->visitor->location);
    }

    /**
     * Retrieves the country code of the visitor.
     *
     * @return string|null The country code, or null if not available.
     */
    public function getCountryCode()
    {
        return $this->visitor->location ?? null;
    }

    /**
     * Retrieves the region of the visitor.
     *
     * @return string|null The region of the visitor, or null if not available.
     */
    public function getRegion()
    {
        return $this->visitor->region ?? null;
    }

    /**
     * Retrieves the city associated with the visitor's location.
     *
     * @return string|null The city name, or null if not available.
     */
    public function getCity()
    {
        return $this->visitor->city ?? null;
    }

    /**
     * Retrieves the continent associated with the visitor's location.
     *
     * @return string|null The continent name, or null if not available.
     */
    public function getContinent()
    {
        return $this->visitor->continent ?? null;
    }
}
