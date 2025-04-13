<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `device_types` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 */
class DeviceTypeRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'device_types';
}
