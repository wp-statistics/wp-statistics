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
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'device_browsers';
}
