<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Record model for the `device_oss` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 */
class DeviceOsRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'device_oss';
}
