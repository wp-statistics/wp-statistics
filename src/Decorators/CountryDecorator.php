<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'countries' table.
 *
 * Provides accessors for each column in the 'countries' schema.
 */
class CountryDecorator
{
    /**
     * The country record.
     *
     * @var object|null
     */
    private $country;

    /**
     * CountryDecorator Constructor.
     *
     * @param object|null $country Record from the 'countries' table or null.
     */
    public function __construct($country)
    {
        $this->country = $country;
    }

    /**
     * Get the country ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return isset($this->country->ID) ? (int)$this->country->ID : null;
    }

    /**
     * Get the country code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->country->code ?? '';
    }

    /**
     * Get the country name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->country->name ?? '';
    }

    /**
     * Get the continent code.
     *
     * @return string
     */
    public function getContinentCode()
    {
        return $this->country->continent_code ?? '';
    }

    /**
     * Get the continent name.
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->country->continent ?? '';
    }
}
