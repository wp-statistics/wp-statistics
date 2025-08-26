<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `timezones` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 *
 * @since 15.0.0
 */
class TimezoneRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'timezones';
}
