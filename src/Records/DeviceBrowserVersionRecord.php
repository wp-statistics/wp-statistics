<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Record model for the `device_browser_versions` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 */
class DeviceBrowserVersionRecord extends BaseRecord
{
    /**
     * Sets the raw table name for this record.
     *
     * @return void
     */
    protected function setTableName()
    {
        $this->tableName = 'device_browser_versions';
    }
}
