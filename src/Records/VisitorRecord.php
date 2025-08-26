<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;
use WP_STATISTICS\TimeZone;

/**
 * Handles database interactions for the `visitors` table.
 *
 * This class relies on BaseRecord for core database operations.
 *
 * @since 15.0.0
 */
class VisitorRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'visitors';
}
