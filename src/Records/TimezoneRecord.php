<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `timezones` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 */
class TimezoneRecord extends BaseRecord
{
    /**
     * Sets the raw table name for this record.
     *
     * @return void
     */
    protected function setTableName()
    {
        $this->tableName = 'timezones';
    }
}
