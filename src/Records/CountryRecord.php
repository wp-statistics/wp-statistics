<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `countries` table.
 *
 * Provides utility methods for fetching countries based on code,
 * continent code, or other identifying fields.
 * This class relies on BaseRecord for all data access functionality.
 */
class CountryRecord extends BaseRecord
{
    /**
     * Sets the raw table name for this record.
     *
     * @return void
     */
    protected function setTableName()
    {
        $this->tableName = 'countries';
    }

    /**
     * Get all countries by continent code (e.g., 'EU', 'AS').
     *
     * @param string $continentCode
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByContinentCode($continentCode)
    {
        return empty($continentCode) ? [] : $this->getAll(['continent_code' => $continentCode]);
    }
}