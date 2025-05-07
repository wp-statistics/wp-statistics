<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'cities' table.
 *
 * Provides accessors for each column in the 'cities' schema.
 */
class CityDecorator
{
    /**
     * The city record.
     *
     * @var object|null
     */
    private $city;

    /**
     * CityDecorator constructor.
     *
     * @param object|null $city A stdClass representing a 'cities' row, or null.
     */
    public function __construct($city)
    {
        $this->city = $city;
    }

    /**
     * Get city ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->city->ID) ? null : (int)$this->city->ID;
    }

    /**
     * Get associated country ID.
     *
     * @return int|null
     */
    public function getCountryId()
    {
        return empty($this->city->country_id) ? null : (int)$this->city->country_id;
    }

    /**
     * Get region code.
     *
     * @return string
     */
    public function getRegionCode()
    {
        return empty($this->city->region_code) ? '' : $this->city->region_code;
    }

    /**
     * Get region name.
     *
     * @return string
     */
    public function getRegionName()
    {
        return empty($this->city->region_name) ? '' : $this->city->region_name;
    }

    /**
     * Get city name.
     *
     * @return string
     */
    public function getCityName()
    {
        return empty($this->city->city_name) ? '' : $this->city->city_name;
    }
}
