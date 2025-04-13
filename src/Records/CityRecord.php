<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `cities` table.
 *
 * Provides methods to retrieve city data based on country, region, or name.
 * This class relies on BaseRecord for all data access functionality.
 */
class CityRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'cities';

    /**
     * Get all cities by country ID.
     *
     * @param int $countryId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByCountryId($countryId)
    {
        return empty($countryId) ? [] : $this->getAll(['country_id' => $countryId]);
    }

    /**
     * Get all cities by region code.
     *
     * @param string $regionCode
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByRegionCode($regionCode)
    {
        return empty($regionCode) ? [] : $this->getAll(['region_code' => $regionCode]);
    }
}
