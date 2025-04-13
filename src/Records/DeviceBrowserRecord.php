<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Record model for the `device_browsers` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 */
class DeviceBrowserRecord extends BaseRecord
{

    /**
     * Sets the raw table name for this record.
     *
     * @return void
     */
    protected function setTableName()
    {
        $this->tableName = 'device_browsers';
    }
}
