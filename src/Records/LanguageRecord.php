<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `languages` table.
 *
 * This class provides convenience methods for retrieving language records
 * by common filters such as region.
 */
class LanguageRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'languages';

    /**
     * Get all languages by region.
     *
     * @param string $region Region code to filter by.
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByRegion($region)
    {
        return empty($region) ? [] : $this->getAll(['region' => $region]);
    }
}
